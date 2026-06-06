<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\FeeStructure;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    private const TABS = ['university', 'courses', 'fees'];

    public function index(Request $request): View
    {
        $tab = in_array($request->query('tab'), self::TABS, true)
            ? $request->query('tab')
            : 'university';

        $search = trim((string) $request->query('q', ''));
        $universityFilter = $request->query('university_id');
        $courseFilter     = $request->query('course_id');

        // Load all universities + courses up-front so the slide-in panel
        // selects and JS-side filtering work without extra round-trips.
        $allUniversities = University::orderBy('name')->get();
        $allCourses = Course::with('university:id,name')->orderBy('name')->get();

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
        $feesQuery = FeeStructure::with(['university:id,name', 'course:id,name,duration_years'])
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
        ]);
    }

    // ────────────────────────────────────────────────────────────────────
    //  Universities
    // ────────────────────────────────────────────────────────────────────

    public function storeUniversity(Request $request): RedirectResponse
    {
        $data = $this->validateUniversity($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('uploads/universities', 'public');
        }

        University::create($data);

        return redirect()
            ->route('master.index', ['tab' => 'university'])
            ->with('status', 'University added.');
    }

    public function updateUniversity(Request $request, University $university): RedirectResponse
    {
        $data = $this->validateUniversity($request, $university->id);

        if ($request->hasFile('image')) {
            if ($university->image_path && Storage::disk('public')->exists($university->image_path)) {
                Storage::disk('public')->delete($university->image_path);
            }
            $data['image_path'] = $request->file('image')->store('uploads/universities', 'public');
        }

        $university->update($data);

        return redirect()
            ->route('master.index', ['tab' => 'university'])
            ->with('status', 'University updated.');
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
        $data['lateral_entry'] = $request->boolean('lateral_entry');
        $course->update($data);

        $this->syncFeeStructureFromCourse($course->fresh());

        return redirect()
            ->route('master.index', ['tab' => 'courses'])
            ->with('status', 'Course updated.');
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
