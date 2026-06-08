<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\FeeStructure;
use App\Models\Student;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    private const TABS = ['university', 'courses', 'fees', 'upgrade'];

    public function index(Request $request): View
    {
        $isAdminViewer = $request->user()->isAdmin();

        // Sub-admins can browse the shared master data but the Upgrade
        // Semester tab is an admin-only control surface — silently
        // redirect them back to the default tab if they try to land on it.
        $tabsForViewer = $isAdminViewer
            ? self::TABS
            : array_values(array_diff(self::TABS, ['upgrade']));

        $tab = in_array($request->query('tab'), $tabsForViewer, true)
            ? $request->query('tab')
            : 'university';

        $search = trim((string) $request->query('q', ''));
        $universityFilter = $request->query('university_id');
        $courseFilter     = $request->query('course_id');

        // Load all universities + courses up-front so the slide-in panel
        // selects and JS-side filtering work without extra round-trips.
        $allUniversities = University::orderBy('name')->get();
        $allCourses = Course::with('university:id,name,type')->orderBy('name')->get();

        // Universities — name-filtered listing.
        $universitiesQuery = University::query()->orderByDesc('id');
        if ($search !== '' && $tab === 'university') {
            $like = '%'.$search.'%';
            $universitiesQuery->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                  ->orWhere('address', 'like', $like)
                  ->orWhere('website', 'like', $like);
            });
        }
        $universities = $universitiesQuery->get();

        // Courses — filtered by university and search when on the courses tab.
        $coursesQuery = Course::with('university:id,name,type')->orderByDesc('id');
        if ($tab === 'courses') {
            if (! empty($universityFilter)) {
                $coursesQuery->where('university_id', (int) $universityFilter);
            }
            if ($search !== '') {
                $like = '%'.$search.'%';
                $coursesQuery->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                      ->orWhere('mode', 'like', $like)
                      ->orWhere('subjects', 'like', $like);
                });
            }
        }
        $courses = $coursesQuery->get();

        // Fee structures — joined with course + university for display.
        $feesQuery = FeeStructure::with([
                'university:id,name',
                'course:id,name,university_id,duration_years,registration_fee,fee_per_sem',
                'course.university:id,name,type',
            ])
            ->orderByDesc('id');
        if ($tab === 'fees') {
            if (! empty($universityFilter)) {
                $feesQuery->where('university_id', (int) $universityFilter);
            }
            if (! empty($courseFilter)) {
                $feesQuery->where('course_id', (int) $courseFilter);
            }
            if ($search !== '') {
                $like = '%'.$search.'%';
                $feesQuery->whereHas('course', fn ($q) => $q->where('name', 'like', $like));
            }
        }
        $fees = $feesQuery->get();

        $stats = [
            'universities' => [
                'total'      => $allUniversities->count(),
                'university' => $allUniversities->where('type', University::TYPE_UNIVERSITY)->count(),
                'board'      => $allUniversities->where('type', University::TYPE_BOARD)->count(),
            ],
            'courses' => [
                'total'         => $allCourses->count(),
                'lateral'       => $allCourses->where('lateral_entry', true)->count(),
                'universities'  => $allCourses->pluck('university_id')->unique()->count(),
            ],
            'fees' => [
                'total'      => FeeStructure::count(),
                'priced'     => FeeStructure::where('fee_per_sem', '>', 0)->count(),
                'free'       => FeeStructure::where('fee_per_sem', '<=', 0)->count(),
            ],
        ];

        // Per-course upgrade-tab rollup: how many students currently sit
        // in each period of each course. For boards the period is a year
        // (course_year), for universities it's the semester column.
        // Sub-admins see their own students only — same scoping rule the
        // students module uses. The semester / course_year columns
        // arrived in the full-admission-form migration; degrade
        // gracefully if a deploy hasn't run that yet.
        $authUser = $request->user();
        $studentScope = Student::query();
        if (! $authUser->isAdmin()) {
            $studentScope->where('created_by', $authUser->id);
        }

        $hasSemester = \Illuminate\Support\Facades\Schema::hasColumn('students', 'semester');
        $hasYear     = \Illuminate\Support\Facades\Schema::hasColumn('students', 'course_year');

        $semBuckets  = collect();
        $yearBuckets = collect();
        $totals      = collect();

        try {
            if ($hasSemester) {
                $semBuckets = (clone $studentScope)
                    ->select('course_id', 'semester', DB::raw('COUNT(*) as total'))
                    ->whereNotNull('course_id')
                    ->whereNotNull('semester')
                    ->groupBy('course_id', 'semester')
                    ->get()
                    ->groupBy('course_id');
            }

            if ($hasYear) {
                $yearBuckets = (clone $studentScope)
                    ->select('course_id', 'course_year', DB::raw('COUNT(*) as total'))
                    ->whereNotNull('course_id')
                    ->whereNotNull('course_year')
                    ->groupBy('course_id', 'course_year')
                    ->get()
                    ->groupBy('course_id');
            }

            $totals = (clone $studentScope)
                ->select('course_id', DB::raw('COUNT(*) as total'))
                ->whereNotNull('course_id')
                ->groupBy('course_id')
                ->pluck('total', 'course_id');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                'master-data upgrade tab rollup failed: '.$e->getMessage()
            );
        }

        $upgradeRows = $allCourses->map(function (Course $c) use ($semBuckets, $yearBuckets, $totals) {
            $isBoard = $c->isBoard();
            $periods = max(1, (int) $c->feePeriodCount());

            $buckets = [];
            $rawBuckets = $isBoard
                ? ($yearBuckets[$c->id] ?? collect())
                : ($semBuckets[$c->id] ?? collect());
            for ($i = 1; $i <= $periods; $i++) {
                $field = $isBoard ? 'course_year' : 'semester';
                $row = $rawBuckets->firstWhere($field, $i);
                $buckets[$i] = (int) ($row->total ?? 0);
            }

            return [
                'id'               => $c->id,
                'name'             => $c->name,
                'university'       => $c->university?->name,
                'is_board'         => $isBoard,
                'periods'          => $periods,
                'period_label'     => $isBoard ? 'Year' : 'Semester',
                'period_short'     => $isBoard ? 'Y' : 'S',
                'current_semester' => max(1, (int) ($c->current_semester ?? 1)),
                'buckets'          => $buckets,
                'student_total'    => (int) ($totals[$c->id] ?? 0),
            ];
        })->values();

        return view('master.index', [
            'tab'              => $tab,
            'search'           => $search,
            'universityFilter' => $universityFilter,
            'courseFilter'     => $courseFilter,
            'universities'     => $universities,
            'courses'          => $courses,
            'fees'             => $fees,
            'allUniversities'  => $allUniversities,
            'allCourses'       => $allCourses,
            'stats'            => $stats,
            'isAdmin'          => $request->user()->isAdmin(),
            'upgradeRows'      => $upgradeRows,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    //  Universities
    // ────────────────────────────────────────────────────────────────────

    public function storeUniversity(Request $request): RedirectResponse
    {
        $data = $this->validateUniversity($request);
        $data = $this->normalizeUniversityData($data);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->safeStoreFile($request->file('image'), 'uploads/universities');
        }

        University::create($data);

        return redirect()
            ->route('master.index', ['tab' => 'university'])
            ->with('status', 'University added.');
    }

    public function updateUniversity(Request $request, University $university): RedirectResponse
    {
        $data = $this->validateUniversity($request, $university->id);
        $data = $this->normalizeUniversityData($data);

        if ($request->hasFile('image')) {
            if ($university->image_path && Storage::disk('public')->exists($university->image_path)) {
                Storage::disk('public')->delete($university->image_path);
            }
            $data['image_path'] = $this->safeStoreFile($request->file('image'), 'uploads/universities');
        }

        $university->update($data);

        return redirect()
            ->route('master.index', ['tab' => 'university'])
            ->with('status', 'University updated.');
    }

    /**
     * Coerce nullable values to the DB defaults — the `universities`
     * table declares `registration_fee` as NOT NULL DEFAULT 0, and the
     * ConvertEmptyStringsToNull middleware turns an empty form input
     * into NULL, which would otherwise blow up the insert with a
     * NOT NULL violation (the 500 the admin was seeing).
     */
    private function normalizeUniversityData(array $data): array
    {
        $data['registration_fee'] = (float) ($data['registration_fee'] ?? 0);
        // 'image' isn't a column on the universities table — strip it so
        // mass-assignment doesn't surface any surprise behavior.
        unset($data['image']);
        return $data;
    }

    /**
     * Persist a file to the public disk, swallowing infra errors (S3
     * misconfig, missing storage symlink, permissions) and logging them
     * instead of bubbling a 500 to the admin. Returns null when the
     * upload failed so callers can decide whether to abort.
     */
    private function safeStoreFile($file, string $bucket): ?string
    {
        try {
            return $file->store($bucket, 'public');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                'safeStoreFile failed: '.$e->getMessage(),
                ['bucket' => $bucket]
            );
            return null;
        }
    }

    public function destroyUniversity(University $university): RedirectResponse
    {
        if ($university->image_path && Storage::disk('public')->exists($university->image_path)) {
            Storage::disk('public')->delete($university->image_path);
        }
        $university->delete();

        return redirect()
            ->route('master.index', ['tab' => 'university'])
            ->with('status', 'University deleted.');
    }

    // ────────────────────────────────────────────────────────────────────
    //  Courses
    // ────────────────────────────────────────────────────────────────────

    public function storeCourse(Request $request): RedirectResponse
    {
        $data = $this->validateCourse($request);
        $data = $this->normalizeCourseData($data);
        $data['lateral_entry'] = $request->boolean('lateral_entry');
        $course = Course::create($data);

        $this->syncFeeStructureFromCourse($course);

        return redirect()
            ->route('master.index', ['tab' => 'courses'])
            ->with('status', 'Course added.');
    }

    public function updateCourse(Request $request, Course $course): RedirectResponse
    {
        $data = $this->validateCourse($request);
        $data = $this->normalizeCourseData($data);
        $data['lateral_entry'] = $request->boolean('lateral_entry');
        $course->update($data);

        $this->syncFeeStructureFromCourse($course->fresh());

        return redirect()
            ->route('master.index', ['tab' => 'courses'])
            ->with('status', 'Course updated.');
    }

    /**
     * Courses have the same NOT NULL DEFAULT 0 trap on the fee columns
     * as universities — coerce nullable validated values to 0 so an
     * empty input doesn't crash the insert.
     */
    private function normalizeCourseData(array $data): array
    {
        $data['registration_fee'] = (float) ($data['registration_fee'] ?? 0);
        $data['fee_per_sem']      = (float) ($data['fee_per_sem'] ?? 0);
        return $data;
    }

    /**
     * Keep the legacy fee_structures row in sync with whatever the course
     * itself now stores. The Fee Structure tab still surfaces the row, but
     * the course form is the single source of truth for fees.
     */
    private function syncFeeStructureFromCourse(Course $course): void
    {
        $fee = (float) $course->fee_per_sem;

        if ($fee <= 0) {
            // No semester fee → no fee structure to track. Drop any
            // stale row from earlier so the listing stays clean.
            FeeStructure::where('course_id', $course->id)->delete();
            return;
        }

        FeeStructure::updateOrCreate(
            ['course_id' => $course->id],
            [
                'university_id' => $course->university_id,
                'fee_per_sem'   => $fee,
            ]
        );
    }

    public function destroyCourse(Course $course): RedirectResponse
    {
        $course->delete();
        return redirect()
            ->route('master.index', ['tab' => 'courses'])
            ->with('status', 'Course deleted.');
    }

    // ────────────────────────────────────────────────────────────────────
    //  Fee Structures
    // ────────────────────────────────────────────────────────────────────

    public function storeFee(Request $request): RedirectResponse
    {
        $data = $this->validateFee($request);

        $course = Course::findOrFail($data['course_id']);

        FeeStructure::create([
            'course_id'     => $course->id,
            'university_id' => $course->university_id,
            'fee_per_sem'   => (float) $course->fee_per_sem,
        ]);

        return redirect()
            ->route('master.index', ['tab' => 'fees'])
            ->with('status', 'Fee structure added — pulled from course fees.');
    }

    public function updateFee(Request $request, FeeStructure $fee): RedirectResponse
    {
        $data = $this->validateFee($request, $fee->id);

        $course = Course::findOrFail($data['course_id']);

        $fee->update([
            'course_id'     => $course->id,
            'university_id' => $course->university_id,
            'fee_per_sem'   => (float) $course->fee_per_sem,
        ]);

        return redirect()
            ->route('master.index', ['tab' => 'fees'])
            ->with('status', 'Fee structure updated — pulled from course fees.');
    }

    public function destroyFee(FeeStructure $fee): RedirectResponse
    {
        $fee->delete();
        return redirect()
            ->route('master.index', ['tab' => 'fees'])
            ->with('status', 'Fee structure deleted.');
    }

    // ────────────────────────────────────────────────────────────────────
    //  Upgrade Semester
    // ────────────────────────────────────────────────────────────────────

    /**
     * Bump every course of the selected university up by one period.
     * Each enrolled student of those courses also moves up:
     *   • boards bump course_year by 1
     *   • universities bump semester by 1
     * The increment is clamped to the course's total period count, so
     * students who are already in the final semester stay put.
     *
     * If university_id is omitted we bump every course on the platform
     * (the header-level "Upgrade All" button uses that).
     */
    public function upgradeSemester(Request $request): RedirectResponse
    {
        $universityId = (int) $request->input('university_id');

        $courses = $universityId
            ? Course::where('university_id', $universityId)->get()
            : Course::all();

        if ($courses->isEmpty()) {
            return redirect()
                ->route('master.index', ['tab' => 'upgrade'])
                ->with('status', 'No courses found for the selected university.');
        }

        $bumpedStudents = 0;

        DB::transaction(function () use ($courses, &$bumpedStudents) {
            foreach ($courses as $course) {
                $periods = max(1, (int) $course->feePeriodCount());
                $isBoard = $course->isBoard();
                $field   = $isBoard ? 'course_year' : 'semester';

                $current = max(1, (int) ($course->current_semester ?? 1));
                if ($current < $periods) {
                    $course->forceFill(['current_semester' => $current + 1])->save();
                }

                $bumpedStudents += Student::where('course_id', $course->id)
                    ->whereNotNull($field)
                    ->where($field, '<', $periods)
                    ->update([$field => DB::raw($field.' + 1')]);
            }
        });

        $uniName = $universityId
            ? (University::find($universityId)?->name ?? 'the selected university')
            : 'every university';

        return redirect()
            ->route('master.index', ['tab' => 'upgrade'])
            ->with('status', "Upgraded {$bumpedStudents} student(s) across {$courses->count()} course(s) of {$uniName}.");
    }

    /**
     * Snap every course of the selected university — grouped by total
     * duration — to a chosen current semester / year. Every enrolled
     * student of those courses is forced to the same value (clamped to
     * the course's total period count and a minimum of 1) so a
     * mistakenly-bumped cohort can be put back where it should be.
     *
     * The payload looks like:
     *   university_id: 7
     *   targets: { "2": 3, "3": 5, "4": 2 }
     *           ^ key  ^ desired sem/year
     *           |
     *           course duration in years (as string from the form)
     */
    public function resetSemester(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'university_id' => ['required', 'integer', 'exists:universities,id'],
            'targets'       => ['required', 'array'],
            'targets.*'     => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $courses = Course::where('university_id', $data['university_id'])->get();

        if ($courses->isEmpty()) {
            return redirect()
                ->route('master.index', ['tab' => 'upgrade'])
                ->with('status', 'No courses to reset for the selected university.');
        }

        $touchedCourses = 0;
        $touchedStudents = 0;

        DB::transaction(function () use ($courses, $data, &$touchedCourses, &$touchedStudents) {
            foreach ($courses as $course) {
                // Match the form's grouping by stringified duration so
                // "2" / "2.0" both resolve to the same target. We round
                // half-years up because the form only offers whole-year
                // selectors.
                $durationKey = (string) (int) ceil((float) $course->duration_years);
                $target = $data['targets'][$durationKey] ?? null;

                if (! $target) {
                    continue;
                }

                $periods = max(1, (int) $course->feePeriodCount());
                $clamped = max(1, min($periods, (int) $target));
                $isBoard = $course->isBoard();
                $field   = $isBoard ? 'course_year' : 'semester';

                if ((int) ($course->current_semester ?? 0) !== $clamped) {
                    $course->forceFill(['current_semester' => $clamped])->save();
                    $touchedCourses++;
                }

                // Force every student of this course to the new value —
                // intentional, since the whole point of reset is to undo
                // a bad bump regardless of where each row ended up.
                $touchedStudents += Student::where('course_id', $course->id)
                    ->update([$field => $clamped]);
            }
        });

        $uniName = University::find($data['university_id'])?->name ?? 'the selected university';

        return redirect()
            ->route('master.index', ['tab' => 'upgrade'])
            ->with('status', "Reset {$touchedStudents} student(s) across {$touchedCourses} course(s) of {$uniName}.");
    }

    // ────────────────────────────────────────────────────────────────────
    //  Validation
    // ────────────────────────────────────────────────────────────────────

    private function validateUniversity(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'address'          => ['nullable', 'string', 'max:1000'],
            'type'             => ['required', 'in:university,board'],
            'website'          => ['nullable', 'string', 'max:255'],
            'registration_fee' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'image'            => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ], [
            'image.image' => 'Image must be a PNG/JPG/WEBP file.',
            'image.max'   => 'Image must be 2MB or smaller.',
        ]);
    }

    private function validateCourse(Request $request): array
    {
        return $request->validate([
            'university_id'    => ['required', 'integer', 'exists:universities,id'],
            'name'             => ['required', 'string', 'max:255'],
            'mode'             => ['nullable', 'string', 'max:30'],
            'duration_years'   => ['required', 'numeric', 'min:0.5', 'max:10'],
            'registration_fee' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'fee_per_sem'      => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'lateral_entry'    => ['nullable'],
            'subjects'         => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function validateFee(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id',
                Rule::unique('fee_structures', 'course_id')->ignore($ignoreId)],
        ], [
            'course_id.unique' => 'A fee structure already exists for this course.',
        ]);
    }
}
