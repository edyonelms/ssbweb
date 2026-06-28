<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Student;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class StudentsController extends Controller
{
    private const STATUS_OPTIONS = ['all', 'active', 'inactive'];

    public function index(Request $request): View
    {
        $filters = $this->resolveFilters($request);

        $query = $this->scopedQuery($filters)
            ->with(['university:id,name,type', 'course:id,name', 'creator:id,name'])
            ->orderByDesc('id');

        if ($filters['search'] !== '') {
            $like = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                  ->orWhere('mobile', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhere('admission_no', 'like', $like)
                  ->orWhere('class_name', 'like', $like)
                  ->orWhere('father_name', 'like', $like)
                  ->orWhere('parent_name', 'like', $like);
            });
        }

        $students = $query->get();

        // Stats reflect the role-scoped set (ignoring chip filters) so
        // they stay stable as the chips toggle.
        $allScoped = $this->roleScopedQuery()->get(['id', 'active']);
        $stats = [
            'total'    => $allScoped->count(),
            'active'   => $allScoped->where('active', true)->count(),
            'inactive' => $allScoped->where('active', false)->count(),
        ];

        // Admin gets a "created by" picker so they can drill into a
        // specific sub-admin's students; sub-admin doesn't see the picker.
        $userOptions = collect();
        if (auth()->user()->isAdmin()) {
            $userOptions = User::where('role', User::ROLE_SUBADMIN)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return view('students.index', [
            'students'        => $students,
            'stats'           => $stats,
            'status'          => $filters['status'],
            'search'          => $filters['search'],
            'universityId'    => $filters['university_id'],
            'courseId'        => $filters['course_id'],
            'createdBy'       => $filters['created_by'],
            'allUniversities' => University::orderBy('name')->get(['id', 'name', 'type']),
            'allCourses'      => Course::orderBy('name')->get(['id', 'name', 'university_id']),
            'userOptions'     => $userOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateStudent($request);
        $data = $this->mergeRequestExtras($request, $data);
        $data = $this->mergeUploads($request, $data, null);
        $data['active']     = $request->boolean('active', true);
        $data['created_by'] = auth()->id();
        $data['admission_no'] = $this->resolveAdmissionNo($data['admission_no'] ?? null);

        Student::create($data);

        return redirect()
            ->route('students.index')
            ->with('status', 'Student added successfully.');
    }

    /**
     * Auto-generate the application / admission number from a 1001
     * baseline so each new student gets the next sequential value.
     * Anything explicitly typed in the form wins — sequence resumes
     * from the highest numeric value on file (any non-numeric values
     * are ignored).
     */
    private function resolveAdmissionNo(?string $provided): string
    {
        $provided = trim((string) $provided);
        if ($provided !== '') {
            return $provided;
        }

        $maxNumeric = Student::query()
            ->whereNotNull('admission_no')
            ->where('admission_no', '!=', '')
            ->pluck('admission_no')
            ->filter(fn ($v) => ctype_digit((string) $v))
            ->map(fn ($v) => (int) $v)
            ->max();

        return (string) max(((int) $maxNumeric) + 1, 1001);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeAccess($student);

        $data = $this->validateStudent($request, $student->id);
        $data = $this->mergeRequestExtras($request, $data);
        $data = $this->mergeUploads($request, $data, $student);
        $data['active'] = $request->boolean('active', true);

        $student->update($data);

        return redirect()
            ->route('students.index')
            ->with('status', 'Student updated successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorizeAccess($student);

        // Clean up any uploaded files; the row is going away so the
        // disk shouldn't keep the originals around.
        foreach (Student::DOCUMENT_FIELDS as $field) {
            $path = $student->{$field};
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('status', 'Student deleted successfully.');
    }

    /**
     * Render (or download) the student's admission form template — a
     * print-friendly standalone HTML page that mirrors the add-student
     * form, populated with the student's saved values plus embedded
     * uploads. When ?download=1 is passed we emit Content-Disposition:
     * attachment so the browser saves it as an .html file the registrar
     * can keep on file or print.
     */
    public function form(Request $request, Student $student): Response
    {
        $this->authorizeAccess($student);

        $student->loadMissing(['university', 'course', 'creator', 'feePayments']);

        $docUrls = collect(Student::DOCUMENT_FIELDS)
            ->mapWithKeys(fn ($f) => [$f => $student->documentUrl($f)])
            ->all();

        // The letterhead admission form is the canonical template — it
        // pulls the university's / board's logo, name, address, website
        // and accreditation badge from master data, so the same layout
        // brands itself for whichever institution the student belongs to.
        $html = view('students.admission-form', [
            'student'  => $student,
            'schedule' => $student->feeSchedule(),
            'docUrls'  => $docUrls,
        ])->render();

        $headers = ['Content-Type' => 'text/html; charset=UTF-8'];

        if ($request->boolean('download')) {
            $slug = Str::slug($student->name ?: 'student').'-'.$student->id;
            $headers['Content-Disposition'] = 'attachment; filename="admission-form-'.$slug.'.html"';
        }

        return response($html, 200, $headers);
    }

    /**
     * Streams every student visible to the caller as a richly-detailed
     * CSV with every field the admission form collects — text,
     * academic-history blob, document URLs — plus total fee, total
     * collected, and remaining balance with a per-period schedule.
     *
     * Honors the same filters the listing uses, so an admin who has
     * picked a sub-admin from the "User" filter only exports that
     * sub-admin's students.
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = $this->resolveFilters($request);

        $students = $this->scopedQuery($filters)
            ->with([
                'university:id,name,type',
                'course',
                'creator:id,name',
                'feePayments',
            ])
            ->orderByDesc('id')
            ->get();

        $filename = 'students_'.now()->format('Y-m-d_H-i-s').'.csv';

        // Stable column order — every text field from the admission
        // form, the academic-history blob, every uploaded document URL,
        // and the fee totals + per-period schedule at the end. The
        // header row labels here drive the order of cell writes below.
        $docLabels = [
            'photo_path'                => 'Photo URL',
            'student_sign_path'         => 'Student Sign URL',
            'aadhar_front_path'         => 'Aadhar Front URL',
            'aadhar_back_path'          => 'Aadhar Back URL',
            'marksheet_x_path'          => 'X Marksheet URL',
            'marksheet_xii_path'        => 'XII Marksheet URL',
            'marksheet_graduation_path' => 'Graduation Marksheet URL',
            'abc_id_path'               => 'ABC ID URL',
            'deb_id_path'               => 'DEB ID URL',
            'other_doc_path'            => 'Other Doc URL',
        ];

        return response()->streamDownload(function () use ($students, $docLabels) {
            $out = fopen('php://output', 'w');

            $header = [
                'ID',
                'Admission No',
                'Name',
                'Father Name',
                'Mother Name',
                'Mobile',
                'Email',
                'Gender',
                'DOB',
                'Category',
                'Nationality',
                'Religion',
                'Aadhar Number',
                'Address',
                'City',
                'State',
                'Country',
                'Pincode',
                'University / Board',
                'Board / University Type',
                'Course',
                'Mode',
                'Type',
                'Course Year',
                'Semester',
                'Class',
                'Parent Name (legacy)',
                'Added By',
                'Status',
                'Created At',
                'Updated At',
                'Academic Records',
            ];
            foreach ($docLabels as $label) {
                $header[] = $label;
            }
            $header[] = 'Total Fee';
            $header[] = 'Total Collected (Paid)';
            $header[] = 'Total Balance';
            $header[] = 'Fee Schedule (per period)';
            fputcsv($out, $header);

            foreach ($students as $s) {
                $schedule  = $s->feeSchedule();
                $totalFee  = array_sum(array_column($schedule, 'fee'));
                $totalPaid = array_sum(array_column($schedule, 'paid'));
                $totalBal  = array_sum(array_column($schedule, 'balance'));

                $scheduleText = collect($schedule)->map(function ($row) {
                    return sprintf(
                        '%s: paid %s / balance %s (fee %s)',
                        $row['label'],
                        number_format($row['paid'], 2),
                        number_format($row['balance'], 2),
                        number_format($row['fee'], 2)
                    );
                })->implode('; ');

                // Academic records collapse to a single human-readable
                // blob so the cell stays sortable in spreadsheets.
                $academicText = collect($s->academic_records ?? [])->map(function ($r) {
                    return ($r['level'] ?? '').': '.collect([
                        'Board: '.($r['board']   ?? ''),
                        'Subject: '.($r['subject'] ?? ''),
                        'Year: '.($r['year']    ?? ''),
                        'Grade: '.($r['grade']   ?? ''),
                    ])->filter(fn ($p) => trim(str_replace(['Board: ', 'Subject: ', 'Year: ', 'Grade: '], '', $p)) !== '')
                      ->implode(', ');
                })->filter()->implode(' | ');

                $row = [
                    $s->id,
                    $s->admission_no,
                    $s->name,
                    $s->father_name,
                    $s->mother_name,
                    $s->mobile,
                    $s->email,
                    $s->gender,
                    $s->dob?->format('Y-m-d'),
                    $s->category,
                    $s->nationality,
                    $s->religion,
                    $s->aadhar_number,
                    $s->address,
                    $s->city,
                    $s->state,
                    $s->country,
                    $s->pincode,
                    $s->university?->name,
                    $s->university?->type,
                    $s->course?->name,
                    $s->mode,
                    $s->enrollment_type,
                    $s->course_year,
                    $s->semester,
                    $s->class_name,
                    $s->parent_name,
                    $s->creator?->name,
                    $s->active ? 'Active' : 'Inactive',
                    $s->created_at?->format('Y-m-d H:i'),
                    $s->updated_at?->format('Y-m-d H:i'),
                    $academicText,
                ];
                foreach (array_keys($docLabels) as $field) {
                    $row[] = $s->documentUrl($field) ?: '';
                }
                $row[] = number_format($totalFee, 2);
                $row[] = number_format($totalPaid, 2);
                $row[] = number_format($totalBal, 2);
                $row[] = $scheduleText;

                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Pull and normalize every filter the listing + export honor so the
     * two flows can never drift apart.
     */
    private function resolveFilters(Request $request): array
    {
        $status = in_array($request->query('status'), self::STATUS_OPTIONS, true)
            ? $request->query('status')
            : 'all';

        $universityId = (int) $request->query('university_id') ?: null;
        $courseId     = (int) $request->query('course_id') ?: null;

        // Admin sees the "User" filter; sub-admin's view is always
        // implicitly self-scoped so we ignore the param for them.
        $createdBy = null;
        if (auth()->user()->isAdmin()) {
            $raw = $request->query('created_by');
            if ($raw === 'self') {
                $createdBy = (int) auth()->id();
            } elseif (is_numeric($raw) && (int) $raw > 0) {
                $createdBy = (int) $raw;
            }
        }

        return [
            'status'        => $status,
            'search'        => trim((string) $request->query('q', '')),
            'university_id' => $universityId,
            'course_id'     => $courseId,
            'created_by'    => $createdBy,
        ];
    }

    /**
     * Role-aware base query (admin → all, sub-admin → own) without
     * chip/filter overlays. Used for stats and as the foundation of
     * scopedQuery().
     */
    private function roleScopedQuery()
    {
        $q = Student::query();
        if (! auth()->user()->isAdmin()) {
            $q->where('created_by', auth()->id());
        }
        return $q;
    }

    /**
     * Role-aware base query + every active filter applied (status,
     * university, course, created_by). Search is layered on top inside
     * index() so the index can still apply it conditionally.
     */
    private function scopedQuery(array $filters = [])
    {
        $q = $this->roleScopedQuery();

        if (($filters['status'] ?? 'all') === 'active') {
            $q->where('active', true);
        } elseif (($filters['status'] ?? 'all') === 'inactive') {
            $q->where('active', false);
        }

        if (! empty($filters['university_id'])) {
            $q->where('university_id', $filters['university_id']);
        }
        if (! empty($filters['course_id'])) {
            $q->where('course_id', $filters['course_id']);
        }
        if (! empty($filters['created_by']) && auth()->user()->isAdmin()) {
            $q->where('created_by', $filters['created_by']);
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
            'name'            => ['required', 'string', 'max:255'],
            'mobile'          => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'email'           => ['nullable', 'email', 'max:255'],
            'admission_no'    => ['nullable', 'string', 'max:50'],
            'class_name'      => ['nullable', 'string', 'max:50'],
            'university_id'   => ['nullable', 'integer', 'exists:universities,id'],
            'course_id'       => ['nullable', 'integer', 'exists:courses,id'],
            'mode'            => ['nullable', 'in:online,offline'],
            'enrollment_type' => ['nullable', 'in:main,lateral,fresh,fresh_board,toc,part,online,odl'],
            'course_year'     => ['nullable', 'integer', 'min:1', 'max:10'],
            'semester'        => ['nullable', 'integer', 'min:1', 'max:20'],
            'father_name'     => ['nullable', 'string', 'max:255'],
            'mother_name'     => ['nullable', 'string', 'max:255'],
            'dob'             => ['nullable', 'date'],
            'category'        => ['nullable', 'in:'.implode(',', Student::CATEGORIES)],
            'nationality'     => ['nullable', 'string', 'max:64'],
            'religion'        => ['nullable', 'string', 'max:64'],
            'aadhar_number'   => ['nullable', 'string', 'max:20'],
            'gender'          => ['nullable', 'in:male,female,other'],
            'parent_name'     => ['nullable', 'string', 'max:255'],
            'address'         => ['nullable', 'string', 'max:1000'],
            'country'         => ['nullable', 'string', 'max:64'],
            'state'           => ['nullable', 'string', 'max:64'],
            'city'            => ['nullable', 'string', 'max:64'],
            'pincode'         => ['nullable', 'string', 'max:12'],

            // Document uploads — all optional. Images allow image/* + pdf
            // because a fair share of marksheets get uploaded as scanned PDFs.
            'photo'                => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'student_sign'         => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'aadhar_front'         => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,pdf', 'max:4096'],
            'aadhar_back'          => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,pdf', 'max:4096'],
            'marksheet_x'          => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,pdf', 'max:4096'],
            'marksheet_xii'        => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,pdf', 'max:4096'],
            'marksheet_graduation' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,pdf', 'max:4096'],
            'abc_id'               => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,pdf', 'max:4096'],
            'deb_id'               => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,pdf', 'max:4096'],
            'other_doc'            => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,pdf', 'max:4096'],
        ], [
            'mobile.regex' => 'Mobile must be 10–15 digits.',
        ]);
    }

    /**
     * Pulls extras the validator doesn't own (academic_records) and
     * stitches them into the data array. Academic records arrive as
     * parallel arrays from the bottom-of-form table; we collapse them
     * into a normalized JSON-friendly list, dropping rows where every
     * field is blank.
     */
    private function mergeRequestExtras(Request $request, array $data): array
    {
        $records = [];
        $levels  = (array) $request->input('academic_level', []);
        $boards  = (array) $request->input('academic_board', []);
        $subj    = (array) $request->input('academic_subject', []);
        $years   = (array) $request->input('academic_year', []);
        $grades  = (array) $request->input('academic_grade', []);

        foreach ($levels as $i => $level) {
            $row = [
                'level'   => (string) ($level ?? ''),
                'board'   => (string) ($boards[$i] ?? ''),
                'subject' => (string) ($subj[$i] ?? ''),
                'year'    => (string) ($years[$i] ?? ''),
                'grade'   => (string) ($grades[$i] ?? ''),
            ];
            // Skip a row where the user filled nothing beyond the level
            // label (X/XII/UG/PG/OTHER are always rendered).
            if ($row['board'] !== '' || $row['subject'] !== '' || $row['year'] !== '' || $row['grade'] !== '') {
                $records[] = $row;
            }
        }

        $data['academic_records'] = $records ?: null;
        return $data;
    }

    /**
     * Stores any uploaded files under uploads/students/<id-or-hash>/
     * and drops the resulting paths onto the data array. When editing,
     * the previous file is removed once the replacement lands.
     */
    private function mergeUploads(Request $request, array $data, ?Student $existing): array
    {
        $map = [
            'photo'                => 'photo_path',
            'student_sign'         => 'student_sign_path',
            'aadhar_front'         => 'aadhar_front_path',
            'aadhar_back'          => 'aadhar_back_path',
            'marksheet_x'          => 'marksheet_x_path',
            'marksheet_xii'        => 'marksheet_xii_path',
            'marksheet_graduation' => 'marksheet_graduation_path',
            'abc_id'               => 'abc_id_path',
            'deb_id'               => 'deb_id_path',
            'other_doc'            => 'other_doc_path',
        ];

        $bucket = 'uploads/students/'.($existing?->id ?? Str::random(8));

        foreach ($map as $input => $column) {
            if (! $request->hasFile($input)) {
                continue;
            }
            if ($existing && $existing->{$column} && Storage::disk('public')->exists($existing->{$column})) {
                Storage::disk('public')->delete($existing->{$column});
            }
            $data[$column] = $request->file($input)->store($bucket, 'public');
        }

        return $data;
    }
}
