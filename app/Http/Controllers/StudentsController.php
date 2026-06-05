<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class StudentsController extends Controller
{
    public function index(): View
    {
        $students = $this->scopedQuery()->orderByDesc('id')->get();

        $stats = [
            'total'    => $students->count(),
            'active'   => $students->where('active', true)->count(),
            'inactive' => $students->where('active', false)->count(),
        ];

        return view('students.index', compact('students', 'stats'));
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
            'name'         => ['required', 'string', 'max:255'],
            'mobile'       => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'email'        => ['nullable', 'email', 'max:255'],
            'admission_no' => ['nullable', 'string', 'max:50'],
            'class_name'   => ['nullable', 'string', 'max:50'],
            'gender'       => ['nullable', 'in:male,female,other'],
            'parent_name'  => ['nullable', 'string', 'max:255'],
            'address'      => ['nullable', 'string', 'max:1000'],
        ], [
            'mobile.regex' => 'Mobile must be 10–15 digits.',
        ]);
    }
}
