<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class StudentsController extends Controller
{
    private const STATUS_OPTIONS = ['all', 'active', 'inactive'];

    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), self::STATUS_OPTIONS, true)
            ? $request->query('status')
            : 'all';

        $search = trim((string) $request->query('q', ''));

        $query = $this->scopedQuery()->orderByDesc('id');

        if ($status === 'active') {
            $query->where('active', true);
        } elseif ($status === 'inactive') {
            $query->where('active', false);
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                  ->orWhere('mobile', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhere('admission_no', 'like', $like)
                  ->orWhere('class_name', 'like', $like)
                  ->orWhere('parent_name', 'like', $like);
            });
        }

        $students = $query->with(['university:id,name,type', 'course:id,name'])->get();

        // Stats reflect the full scoped set so they stay stable as chips toggle.
        $allScoped = $this->scopedQuery()->get(['id', 'active']);
        $stats = [
            'total'    => $allScoped->count(),
            'active'   => $allScoped->where('active', true)->count(),
            'inactive' => $allScoped->where('active', false)->count(),
        ];

        return view('students.index', [
            'students'         => $students,
            'stats'            => $stats,
            'status'           => $status,
            'search'           => $search,
            'allUniversities'  => University::orderBy('name')->get(['id', 'name', 'type']),
            'allCourses'       => Course::orderBy('name')->get(['id', 'name', 'university_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateStudent($request);
        $data['active'] = $request->boolean('active', true);
        $data['created_by'] = auth()->id();

        Student::create($data);

        return redirect()
            ->route('students.index')
            ->with('status', 'Student added successfully.');
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeAccess($student);

        $data = $this->validateStudent($request, $student->id);
        $data['active'] = $request->boolean('active', true);

        $student->update($data);

        return redirect()
            ->route('students.index')
            ->with('status', 'Student updated successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorizeAccess($student);

        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('status', 'Student deleted successfully.');
    }

    public function export(): StreamedResponse
    {
        $students = $this->scopedQuery()->orderByDesc('id')->get();

        $filename = 'students_'.now()->format('Y-m-d_H-i-s').'.csv';

        return response()->streamDownload(function () use ($students) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Mobile', 'Email', 'Admission No', 'Class', 'Gender', 'Parent', 'Address', 'Status', 'Created']);
            foreach ($students as $s) {
                fputcsv($out, [
                    $s->name,
                    $s->mobile,
                    $s->email,
                    $s->admission_no,
                    $s->class_name,
                    $s->gender,
                    $s->parent_name,
                    $s->address,
                    $s->active ? 'Active' : 'Inactive',
                    $s->created_at?->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Admin sees every student; sub-admins only see records they created.
     */
    private function scopedQuery()
    {
        $q = Student::query();
        if (! auth()->user()->isAdmin()) {
            $q->where('created_by', auth()->id());
        }
        return $q;
    }

    private function authorizeAccess(Student $student): void
    {
        if (! auth()->user()->isAdmin() && $student->created_by !== auth()->id()) {
            abort(403);
        }
    }

    private function validateStudent(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'mobile'        => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'email'         => ['nullable', 'email', 'max:255'],
            'admission_no'  => ['nullable', 'string', 'max:50'],
            'class_name'    => ['nullable', 'string', 'max:50'],
            'university_id' => ['nullable', 'integer', 'exists:universities,id'],
            'course_id'     => ['nullable', 'integer', 'exists:courses,id'],
            'gender'        => ['nullable', 'in:male,female,other'],
            'parent_name'   => ['nullable', 'string', 'max:255'],
            'address'       => ['nullable', 'string', 'max:1000'],
        ], [
            'mobile.regex' => 'Mobile must be 10–15 digits.',
        ]);
    }
}
