<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PayFeeController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();

        $universityId = $request->query('university_id');
        $studentId    = $request->query('student_id');
        $search       = trim((string) $request->query('q', ''));

        $universities = University::orderBy('name')->get(['id', 'name', 'type']);

        // Sub-admin scope mirrors the Students module: they only see records
        // they created, the admin sees everyone.
        $studentsQuery = Student::query()->orderBy('name');
        if (! $isAdmin) {
            $studentsQuery->where('created_by', $user->id);
        }
        if (! empty($universityId)) {
            $studentsQuery->where('university_id', (int) $universityId);
        }
        if ($search !== '') {
            $like = '%'.$search.'%';
            $studentsQuery->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                  ->orWhere('mobile', 'like', $like)
                  ->orWhere('admission_no', 'like', $like)
                  ->orWhere('parent_name', 'like', $like);
            });
        }
        $students = $studentsQuery->get(['id', 'name', 'mobile', 'admission_no', 'university_id', 'course_id']);

        // If a student is selected, load full details + the fee schedule.
        $student   = null;
        $schedule  = [];
        $payments  = collect();
        $totals    = ['fee' => 0.0, 'paid' => 0.0, 'balance' => 0.0];

        if (! empty($studentId)) {
            $student = Student::with(['university:id,name,type', 'course:id,name,duration_years,registration_fee,fee_per_sem'])
                ->find((int) $studentId);

            if ($student && (! $isAdmin) && $student->created_by !== $user->id) {
                $student = null; // sub-admin can't view other people's students
            }

            if ($student) {
                $schedule = $student->feeSchedule();
                $payments = $student->feePayments()->with('recordedBy:id,name')->get();

                $totals['fee']     = array_sum(array_column($schedule, 'fee'));
                $totals['paid']    = array_sum(array_column($schedule, 'paid'));
                $totals['balance'] = max(0, $totals['fee'] - $totals['paid']);
            }
        }

        return view('pay-fee.index', [
            'isAdmin'      => $isAdmin,
            'authUser'     => $user,
            'universities' => $universities,
            'students'     => $students,
            'universityId' => $universityId,
            'studentId'    => $studentId,
            'search'       => $search,
            'student'      => $student,
            'schedule'     => $schedule,
            'payments'     => $payments,
            'totals'       => $totals,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'        => ['required', 'integer', 'exists:students,id'],
            'start_semester'    => ['required', 'integer', 'min:0', 'max:50'],
            'amount'            => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'mode'              => ['required', 'in:'.implode(',', FeePayment::MODES)],
            'remark'            => ['nullable', 'string', 'max:500'],
            'collected_by_name' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $isAdmin = $user->isAdmin();

        $student = Student::with('course')->findOrFail((int) $data['student_id']);

        if (! $isAdmin && $student->created_by !== $user->id) {
            abort(403);
        }

        if (! $student->course) {
            throw ValidationException::withMessages([
                'student_id' => 'This student is not linked to a course yet — set university & course on the student first.',
            ]);
        }

        $schedule = $student->feeSchedule();
        if (empty($schedule)) {
            throw ValidationException::withMessages([
                'student_id' => 'No fee schedule for this student — make sure the course has fees set.',
            ]);
        }

        // Build a quick map: semester index → remaining balance, in the
        // schedule's natural order (registration first if any, then sems).
        $startSem = (int) $data['start_semester'];
        $remainingAmount = (float) $data['amount'];
        $allocations = []; // [['semester'=>i, 'amount'=>x], ...]

        $reachedStart = false;
        foreach ($schedule as $row) {
            if ($row['semester'] === $startSem) {
                $reachedStart = true;
            }
            if (! $reachedStart) {
                continue; // skip semesters before the picked starting point
            }
            if ($remainingAmount <= 0) {
                break;
            }
            $balance = (float) $row['balance'];
            if ($balance <= 0) {
                continue; // already paid in full — skip but keep walking
            }
            $take = min($remainingAmount, $balance);
            $allocations[] = ['semester' => $row['semester'], 'amount' => $take];
            $remainingAmount -= $take;
        }

        if (! $reachedStart) {
            throw ValidationException::withMessages([
                'start_semester' => 'Picked starting semester is not part of this course.',
            ]);
        }

        if ($remainingAmount > 0.009) {
            throw ValidationException::withMessages([
                'amount' => 'Amount exceeds the outstanding balance ('
                    .'₹'.number_format((float) $data['amount'] - $remainingAmount, 2)
                    .' would be applied; ₹'.number_format($remainingAmount, 2).' is extra).',
            ]);
        }

        if (empty($allocations)) {
            throw ValidationException::withMessages([
                'amount' => 'Nothing to pay — the selected semester and the ones after it are already cleared.',
            ]);
        }

        $batchId = (string) Str::uuid();
        $paidAt  = now();

        DB::transaction(function () use ($allocations, $student, $data, $user, $batchId, $paidAt) {
            foreach ($allocations as $alloc) {
                FeePayment::create([
                    'student_id'        => $student->id,
                    'semester'          => $alloc['semester'],
                    'amount'            => $alloc['amount'],
                    'mode'              => $data['mode'],
                    'collected_by_name' => $data['collected_by_name'],
                    'remark'            => $data['remark'] ?? null,
                    'recorded_by'       => $user->id,
                    'batch_id'          => $batchId,
                    'paid_at'           => $paidAt,
                ]);
            }
        });

        ActivityLog::record(
            'fee.paid',
            'Collected ₹'.number_format((float) $data['amount'], 2).' from '.$student->name,
            $student,
            [
                'student_id' => $student->id,
                'amount'     => (float) $data['amount'],
                'mode'       => $data['mode'],
                'batch_id'   => $batchId,
                'splits'     => $allocations,
            ]
        );

        return redirect()
            ->route('pay-fee.index', [
                'university_id' => $student->university_id,
                'student_id'    => $student->id,
            ])
            ->with('status', 'Fee payment recorded ('.count($allocations).' semester'
                .(count($allocations) === 1 ? '' : 's').').');
    }

    public function destroy(Request $request, FeePayment $feePayment): RedirectResponse
    {
        $user = $request->user();

        // Only admin can delete posted fee payments to keep audit trails clean.
        abort_unless($user->isAdmin(), 403);

        $student = $feePayment->student;
        $feePayment->delete();

        ActivityLog::record(
            'fee.payment_removed',
            'Removed a ₹'.number_format((float) $feePayment->amount, 2).' fee entry for '.($student?->name ?? 'student'),
            $student
        );

        return back()->with('status', 'Fee payment entry removed.');
    }
}
