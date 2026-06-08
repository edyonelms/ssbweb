@extends('layouts.admin')

@section('title', 'Master Data - SSB Education')

@php
    /** @var string $tab */
    /** @var string $search */
    /** @var int|null|string $universityFilter */
    /** @var int|null|string $courseFilter */
    /** @var \Illuminate\Support\Collection $universities */
    /** @var \Illuminate\Support\Collection $courses */
    /** @var \Illuminate\Support\Collection $fees */
    /** @var \Illuminate\Support\Collection $allUniversities */
    /** @var \Illuminate\Support\Collection $allCourses */
    /** @var array $stats */
    /** @var bool $isAdmin */

    $tabs = [
        'university' => 'University',
        'courses'    => 'Courses',
        'fees'       => 'Fee Structure',
    ];

    // Upgrade Semester is an admin-only management surface — sub-admin
    // sees current sem per student on the students listing and per
    // course on the courses tab, but the control row stays out of their
    // navigation entirely.
    if ($isAdmin) {
        $tabs['upgrade'] = 'Upgrade Semester';
    }

    $tabUrl = function (string $key) {
        return route('master.index', ['tab' => $key]);
    };

    $buildUrl = function (array $overrides) use ($tab, $search, $universityFilter, $courseFilter) {
        $params = array_filter(array_merge([
            'tab'           => $tab,
            'q'             => $search !== '' ? $search : null,
            'university_id' => $universityFilter ?: null,
            'course_id'     => $courseFilter ?: null,
        ], $overrides), fn ($v) => $v !== null && $v !== '');
        return route('master.index').'?'.http_build_query($params);
    };

    // JSON blobs powering the slide-in panel.
    $universitiesData = $universities->map(fn ($u) => [
        'id'               => $u->id,
        'name'             => $u->name,
        'image_url'        => $u->image_url,
        'address'          => $u->address,
        'type'             => $u->type,
        'website'          => $u->website,
        'registration_fee' => (float) $u->registration_fee,
        'created_at'       => $u->created_at?->format('d M Y'),
    ])->keyBy('id');

    $coursesData = $courses->map(fn ($c) => [
        'id'               => $c->id,
        'university_id'    => $c->university_id,
        'university'       => $c->university?->name,
        'name'             => $c->name,
        'mode'             => $c->mode,
        'duration_years'   => (float) $c->duration_years,
        'registration_fee' => (float) $c->registration_fee,
        'fee_per_sem'      => (float) $c->fee_per_sem,
        'total_fee'        => $c->totalFee(),
        'lateral_entry'    => (bool) $c->lateral_entry,
        'subjects'         => $c->subjects,
        'semesters'        => $c->semesterCount(),
        'is_board'         => $c->isBoard(),
        'fee_period_count' => $c->feePeriodCount(),
        'fee_period_label' => $c->feePeriodLabel(),
        'current_period'   => $c->currentPeriodLabel(),
        'current_semester' => (int) ($c->current_semester ?? 1),
        'created_at'       => $c->created_at?->format('d M Y'),
    ])->keyBy('id');

    $allCoursesData = $allCourses->map(fn ($c) => [
        'id'               => $c->id,
        'university_id'    => $c->university_id,
        'name'             => $c->name,
        'duration_years'   => (float) $c->duration_years,
        'registration_fee' => (float) $c->registration_fee,
        'fee_per_sem'      => (float) $c->fee_per_sem,
        'semesters'        => $c->semesterCount(),
        'is_board'         => $c->isBoard(),
        'fee_period_count' => $c->feePeriodCount(),
        'fee_period_label' => $c->feePeriodLabel(),
        'total_fee'        => $c->totalFee(),
    ])->values();

    $feesData = $fees->map(fn ($f) => [
        'id'               => $f->id,
        'university_id'    => $f->university_id,
        'university'       => $f->university?->name,
        'course_id'        => $f->course_id,
        'course'           => $f->course?->name,
        'duration_years'   => (float) ($f->course?->duration_years ?? 0),
        'semesters'        => $f->course?->semesterCount() ?? 0,
        'registration_fee' => (float) ($f->course?->registration_fee ?? 0),
        'fee_per_sem'      => (float) ($f->course?->fee_per_sem ?? $f->fee_per_sem),
        'total_fee'        => (float) ($f->course?->totalFee() ?? $f->totalFee()),
        'is_board'         => (bool) $f->course?->isBoard(),
        'fee_period_count' => (int) ($f->course?->feePeriodCount() ?? 0),
        'fee_period_label' => $f->course?->feePeriodLabel() ?? 'Semester',
        'created_at'       => $f->created_at?->format('d M Y'),
    ])->keyBy('id');
@endphp

@section('admin-header')
<div class="sticky top-0 z-20 bg-white border-b border-slate-200">

    {{-- ROW 1 — Header: title + subtitle + per-tab analytics + Add button --}}
    <div class="px-6 lg:px-10 py-3 flex flex-wrap items-center gap-x-6 gap-y-2 border-b border-slate-100">
        <div class="mr-auto">
            <h2 class="text-base font-bold text-slate-800">Master Data</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                @if ($tab === 'university')
                    Manage universities &amp; boards used across the platform
                @elseif ($tab === 'courses')
                    Programs offered by each university or board
                @elseif ($tab === 'fees')
                    Auto-synced from the course form (annual for boards, per-semester for universities)
                @else
                    Track which semester (or year, for boards) is currently running per course
                @endif
            </p>
        </div>

        @if ($tab === 'university')
            <div class="flex items-center gap-x-6 gap-y-1 text-xs text-slate-500 flex-wrap">
                <span>Total: <span class="text-slate-800 font-semibold ml-1">{{ $stats['universities']['total'] }}</span></span>
                <span>Universities: <span class="text-pink-600 font-semibold ml-1">{{ $stats['universities']['university'] }}</span></span>
                <span>Boards: <span class="text-emerald-600 font-semibold ml-1">{{ $stats['universities']['board'] }}</span></span>
            </div>
            @if ($isAdmin)
                <button type="button" onclick="MasterPanel.openCreate('university')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add University
                </button>
            @endif
        @elseif ($tab === 'courses')
            <div class="flex items-center gap-x-6 gap-y-1 text-xs text-slate-500 flex-wrap">
                <span>Total: <span class="text-slate-800 font-semibold ml-1">{{ $stats['courses']['total'] }}</span></span>
                <span>Universities: <span class="text-pink-600 font-semibold ml-1">{{ $stats['courses']['universities'] }}</span></span>
                <span>Lateral Entry: <span class="text-emerald-600 font-semibold ml-1">{{ $stats['courses']['lateral'] }}</span></span>
            </div>
            @if ($isAdmin)
                <button type="button" onclick="MasterPanel.openCreate('course')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Add Course
                </button>
            @endif
        @elseif ($tab === 'fees')
            <div class="flex items-center gap-x-6 gap-y-1 text-xs text-slate-500 flex-wrap">
                <span>Total: <span class="text-slate-800 font-semibold ml-1">{{ $stats['fees']['total'] }}</span></span>
                <span>Priced: <span class="text-pink-600 font-semibold ml-1">{{ $stats['fees']['priced'] }}</span></span>
                <span>Free: <span class="text-emerald-600 font-semibold ml-1">{{ $stats['fees']['free'] }}</span></span>
            </div>
            {{-- Fee structures are auto-synced from the course form; no manual add button. --}}
        @else
            @php
                $totalStudentsAcross = $upgradeRows->sum('student_total');
                $coursesWithEnroll   = $upgradeRows->filter(fn ($r) => $r['student_total'] > 0)->count();
            @endphp
            <div class="flex items-center gap-x-6 gap-y-1 text-xs text-slate-500 flex-wrap">
                <span>Courses: <span class="text-slate-800 font-semibold ml-1">{{ $upgradeRows->count() }}</span></span>
                <span>With Students: <span class="text-pink-600 font-semibold ml-1">{{ $coursesWithEnroll }}</span></span>
                <span>Total Students: <span class="text-emerald-600 font-semibold ml-1">{{ $totalStudentsAcross }}</span></span>
            </div>
            {{-- No header-level "upgrade all" button — the per-university
                 form below is the canonical entry point. --}}
        @endif
    </div>

    {{-- ROW 2 — Three tabs (University / Courses / Fee Structure) --}}
    <div class="px-4 lg:px-8 flex gap-0 overflow-x-auto border-b border-slate-100" role="tablist">
        @foreach ($tabs as $key => $label)
            @php $isActive = $tab === $key; @endphp
            <a href="{{ $tabUrl($key) }}"
               class="relative px-3 sm:px-4 py-2.5 text-sm font-medium whitespace-nowrap transition
                      {{ $isActive ? 'text-pink-600 font-semibold' : 'text-slate-500 hover:text-pink-600' }}">
                {{ $label }}
                @if ($isActive)
                    <span class="absolute left-3 right-3 sm:left-4 sm:right-4 bottom-0 h-0.5 rounded-full bg-pink-500"></span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- ROW 3 — Filter row (per tab). The Upgrade Semester tab doesn't
         have searchable rows, so we skip the row entirely there. --}}
    @if ($tab !== 'upgrade')
    <div class="px-6 lg:px-10 py-2.5 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs">
        <div class="flex items-center gap-1.5 text-slate-500">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <span class="font-semibold text-slate-600">Filter by:</span>
        </div>

        @if ($tab === 'courses' || $tab === 'fees')
            @php
                $unisOnly   = $allUniversities->where('type', \App\Models\University::TYPE_UNIVERSITY);
                $boardsOnly = $allUniversities->where('type', \App\Models\University::TYPE_BOARD);
                // On the Fee Structure tab we offer a second dropdown to
                // pick a course. It only lists courses from the currently
                // chosen university — pick a different university and the
                // course filter resets.
                $coursesForFilter = $universityFilter
                    ? $allCourses->where('university_id', (int) $universityFilter)
                    : $allCourses;
            @endphp
            <form method="GET" action="{{ route('master.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                @if ($search !== '')
                    <input type="hidden" name="q" value="{{ $search }}">
                @endif
                <span class="text-slate-500">University / Board:</span>
                <select name="university_id"
                        onchange="const c=this.form.querySelector('[name=&quot;course_id&quot;]'); if (c) c.value=''; this.form.submit()"
                        class="px-3 py-1.5 bg-white border border-slate-200 rounded-full text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 transition">
                    <option value="">All</option>
                    @if ($unisOnly->isNotEmpty())
                        <optgroup label="Universities">
                            @foreach ($unisOnly as $u)
                                <option value="{{ $u->id }}" {{ (string) $universityFilter === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if ($boardsOnly->isNotEmpty())
                        <optgroup label="Boards">
                            @foreach ($boardsOnly as $u)
                                <option value="{{ $u->id }}" {{ (string) $universityFilter === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>

                @if ($tab === 'fees')
                    <span class="text-slate-500">Course:</span>
                    <select name="course_id" onchange="this.form.submit()"
                            class="px-3 py-1.5 bg-white border border-slate-200 rounded-full text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 transition">
                        <option value="">All</option>
                        @foreach ($coursesForFilter as $c)
                            <option value="{{ $c->id }}" {{ (string) $courseFilter === (string) $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </form>
        @endif

        <form method="GET" action="{{ route('master.index') }}" class="ml-auto flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if (! empty($universityFilter))
                <input type="hidden" name="university_id" value="{{ $universityFilter }}">
            @endif
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>
                </div>
                <input type="text" name="q" value="{{ $search }}"
                       placeholder="Search..."
                       class="w-56 sm:w-64 pl-7 pr-3 py-1.5 bg-white border border-slate-200 rounded-full text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 transition">
            </div>
            <button type="submit"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold bg-pink-600 hover:bg-pink-700 text-white transition">
                Search
            </button>
            @if ($search !== '')
                <a href="{{ $buildUrl(['q' => null]) }}"
                   class="px-2 py-1.5 rounded-full text-xs font-semibold text-slate-500 hover:bg-slate-100 transition" title="Clear search">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </form>
    </div>
    @endif
</div>
@endsection

@section('admin')
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

    {{-- ────────────── UNIVERSITY TAB ────────────── --}}
    @if ($tab === 'university')
        @if ($universities->isEmpty())
            @include('master._empty', ['icon' => 'building', 'title' => 'No universities yet', 'subtitle' => 'Add your first university or board to get started.', 'action' => $isAdmin ? 'university' : null])
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-[11px] font-semibold tracking-wider uppercase text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-6 py-3">Name</th>
                            <th class="text-left px-6 py-3">Type</th>
                            <th class="text-left px-6 py-3">Website</th>
                            <th class="text-right px-6 py-3">Reg. Fee</th>
                            @if ($isAdmin)<th class="text-right px-6 py-3">Actions</th>@endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($universities as $u)
                            <tr class="hover:bg-slate-50 transition cursor-pointer" onclick="MasterPanel.openView('university', {{ $u->id }})">
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        @if ($u->image_url)
                                            <img src="{{ $u->image_url }}" alt="" class="w-9 h-9 rounded-md object-cover bg-slate-100">
                                        @else
                                            <div class="w-9 h-9 rounded-md bg-pink-50 text-pink-600 font-bold text-sm flex items-center justify-center">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</div>
                                        @endif
                                        <div>
                                            <div class="font-medium text-slate-800">{{ $u->name }}</div>
                                            @if ($u->address)<div class="text-xs text-slate-500 line-clamp-1">{{ $u->address }}</div>@endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    @if ($u->type === 'board')
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700">Board</span>
                                    @else
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-pink-50 text-pink-700">University</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-slate-600 max-w-xs truncate">{{ $u->website ?: '—' }}</td>
                                <td class="px-6 py-3 text-right text-slate-700 font-medium">₹{{ number_format((float) $u->registration_fee) }}</td>
                                @if ($isAdmin)
                                    <td class="px-6 py-3">
                                        <div class="flex items-center justify-end gap-1" onclick="event.stopPropagation()">
                                            <button type="button" onclick="MasterPanel.openEdit('university', {{ $u->id }})" title="Edit" class="w-8 h-8 rounded-md text-slate-500 hover:bg-slate-100 hover:text-pink-600 inline-flex items-center justify-center transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <form method="POST" action="{{ route('master.universities.destroy', $u) }}"
                                                  onsubmit="return confirmAction(this, 'Delete this university? All linked courses and fee structures will also be removed.', 'Delete university');">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Delete" class="w-8 h-8 rounded-md text-slate-500 hover:bg-slate-100 hover:text-rose-600 inline-flex items-center justify-center transition">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    {{-- ────────────── COURSES TAB ────────────── --}}
    @elseif ($tab === 'courses')
        @if ($courses->isEmpty())
            @include('master._empty', ['icon' => 'cap', 'title' => 'No courses yet', 'subtitle' => 'Pick a university and add the courses it offers.', 'action' => $isAdmin ? 'course' : null])
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-[11px] font-semibold tracking-wider uppercase text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-6 py-3">Course</th>
                            <th class="text-left px-6 py-3">University</th>
                            <th class="text-left px-6 py-3">Mode</th>
                            <th class="text-right px-6 py-3">Duration</th>
                            <th class="text-right px-6 py-3">Reg. Fee</th>
                            <th class="text-right px-6 py-3">Fee / Period</th>
                            <th class="text-left px-6 py-3">Lateral</th>
                            @if ($isAdmin)<th class="text-right px-6 py-3">Actions</th>@endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($courses as $c)
                            @php
                                $rowIsBoard = $c->isBoard();
                                $rowShort   = $rowIsBoard ? 'yr' : 'sem';
                                $rowDurExtra= $rowIsBoard ? '' : ' · '.$c->semesterCount().' sem';
                            @endphp
                            <tr class="hover:bg-slate-50 transition cursor-pointer" onclick="MasterPanel.openView('course', {{ $c->id }})">
                                <td class="px-6 py-3">
                                    <div class="font-medium text-slate-800">{{ $c->name }}</div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        @if ($c->subjects)<div class="text-xs text-slate-500 line-clamp-1">{{ $c->subjects }}</div>@endif
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-pink-50 text-pink-700"
                                              title="Currently running">
                                            {{ $c->currentPeriodLabel() }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-slate-600">{{ $c->university?->name ?: '—' }}</td>
                                <td class="px-6 py-3 text-slate-600 capitalize">{{ $c->mode ?: '—' }}</td>
                                <td class="px-6 py-3 text-right text-slate-700">{{ rtrim(rtrim(number_format((float) $c->duration_years, 1), '0'), '.') }} yrs{{ $rowDurExtra }}</td>
                                <td class="px-6 py-3 text-right text-slate-700">₹{{ number_format((float) $c->registration_fee) }}</td>
                                <td class="px-6 py-3 text-right text-slate-700 font-medium">₹{{ number_format((float) $c->fee_per_sem) }} <span class="text-[10px] text-slate-400">/{{ $rowShort }}</span></td>
                                <td class="px-6 py-3">
                                    @if ($c->lateral_entry)
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700">Yes</span>
                                    @else
                                        <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">No</span>
                                    @endif
                                </td>
                                @if ($isAdmin)
                                    <td class="px-6 py-3">
                                        <div class="flex items-center justify-end gap-1" onclick="event.stopPropagation()">
                                            <button type="button" onclick="MasterPanel.openEdit('course', {{ $c->id }})" title="Edit" class="w-8 h-8 rounded-md text-slate-500 hover:bg-slate-100 hover:text-pink-600 inline-flex items-center justify-center transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <form method="POST" action="{{ route('master.courses.destroy', $c) }}"
                                                  onsubmit="return confirmAction(this, 'Delete this course? The linked fee structure will also be removed.', 'Delete course');">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Delete" class="w-8 h-8 rounded-md text-slate-500 hover:bg-slate-100 hover:text-rose-600 inline-flex items-center justify-center transition">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    {{-- ────────────── FEE STRUCTURE TAB ────────────── --}}
    @elseif ($tab === 'fees')
        @if ($fees->isEmpty())
            @include('master._empty', ['icon' => 'fee', 'title' => 'No fee structures yet', 'subtitle' => 'Fee structures appear here automatically once a course has a fee set.', 'action' => null])
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-[11px] font-semibold tracking-wider uppercase text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-6 py-3">University</th>
                            <th class="text-left px-6 py-3">Course</th>
                            <th class="text-right px-6 py-3">Periods</th>
                            <th class="text-right px-6 py-3">Reg. Fee</th>
                            <th class="text-right px-6 py-3">Fee / Period</th>
                            <th class="text-right px-6 py-3">Total Fee</th>
                            @if ($isAdmin)<th class="text-right px-6 py-3">Actions</th>@endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($fees as $f)
                            @php
                                $isBoardRow = $f->course?->isBoard() ?? false;
                                $periodCount = $f->course?->feePeriodCount() ?? 0;
                                $periodShort = $isBoardRow ? 'yr' : 'sem';
                            @endphp
                            <tr class="hover:bg-slate-50 transition cursor-pointer" onclick="MasterPanel.openView('fee', {{ $f->id }})">
                                <td class="px-6 py-3 text-slate-600">{{ $f->university?->name ?: '—' }}</td>
                                <td class="px-6 py-3">
                                    <div class="font-medium text-slate-800">{{ $f->course?->name ?: '—' }}</div>
                                    <div class="text-xs text-slate-500">{{ rtrim(rtrim(number_format((float) ($f->course?->duration_years ?? 0), 1), '0'), '.') }} years</div>
                                </td>
                                <td class="px-6 py-3 text-right text-slate-700">{{ $periodCount }} {{ $periodShort }}</td>
                                <td class="px-6 py-3 text-right text-slate-700">₹{{ number_format((float) ($f->course?->registration_fee ?? 0)) }}</td>
                                <td class="px-6 py-3 text-right text-slate-700 font-medium">₹{{ number_format((float) ($f->course?->fee_per_sem ?? $f->fee_per_sem)) }} <span class="text-[10px] text-slate-400">/{{ $periodShort }}</span></td>
                                <td class="px-6 py-3 text-right text-pink-600 font-semibold">₹{{ number_format($f->course?->totalFee() ?? $f->totalFee()) }}</td>
                                @if ($isAdmin)
                                    <td class="px-6 py-3">
                                        <div class="flex items-center justify-end gap-1" onclick="event.stopPropagation()">
                                            <button type="button" onclick="MasterPanel.openEdit('fee', {{ $f->id }})" title="Edit" class="w-8 h-8 rounded-md text-slate-500 hover:bg-slate-100 hover:text-pink-600 inline-flex items-center justify-center transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <form method="POST" action="{{ route('master.fees.destroy', $f) }}"
                                                  onsubmit="return confirmAction(this, 'Delete this fee structure?', 'Delete fee structure');">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="Delete" class="w-8 h-8 rounded-md text-slate-500 hover:bg-slate-100 hover:text-rose-600 inline-flex items-center justify-center transition">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    {{-- ────────────── UPGRADE SEMESTER TAB ──────────────
         Admin-only by route guard. Two stacked forms — Upgrade (bump
         every course of a chosen university by one period) and Reset
         (snap every course of a chosen university to a specific
         period, grouped by course duration). The same per-course
         rollup from before sits below for context. --}}
    @else
        {{-- Build a uni → { duration => [course names] } map so the
             Reset form can render one selector per distinct course
             duration the chosen university offers. The DOM is pre-
             rendered for every university and a tiny JS toggle reveals
             the group that matches the user's pick — no extra round
             trips. --}}
        @php
            $upgradeUnis = $allUniversities->mapWithKeys(function ($u) use ($allCourses) {
                $coursesOfUni = $allCourses->where('university_id', $u->id);
                $byDuration = [];
                foreach ($coursesOfUni as $c) {
                    $durKey = (int) ceil((float) $c->duration_years);
                    $byDuration[$durKey][] = [
                        'id'       => $c->id,
                        'name'     => $c->name,
                        'is_board' => $c->isBoard(),
                        'periods'  => $c->feePeriodCount(),
                    ];
                }
                ksort($byDuration);
                return [$u->id => [
                    'name'        => $u->name,
                    'type'        => $u->type,
                    'is_board'    => $u->type === \App\Models\University::TYPE_BOARD,
                    'by_duration' => $byDuration,
                    'total'       => $coursesOfUni->count(),
                ]];
            });
        @endphp

        <div class="p-6 lg:p-8 grid grid-cols-1 lg:grid-cols-2 gap-6 border-b border-slate-100">
            {{-- UPGRADE form --}}
            <form method="POST" action="{{ route('master.upgrade.semester') }}"
                  onsubmit="return confirmAction(this, 'Move every enrolled student of this university up by one period (semester for universities, year for boards). Continue?', 'Upgrade university', { tone: 'emerald', confirmLabel: 'Upgrade' });"
                  class="bg-gradient-to-br from-emerald-50 via-white to-white border border-emerald-100 rounded-xl p-5 flex flex-col">
                @csrf
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-600 text-white flex items-center justify-center">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Upgrade Semester</h3>
                        <p class="text-[11px] text-slate-500">Bump every course of one university up by one period.</p>
                    </div>
                </div>

                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select University / Board</label>
                <select name="university_id" required
                        class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-300/60 focus:border-emerald-300/60 transition">
                    <option value="">— Pick a university or board —</option>
                    @foreach ($upgradeUnis as $id => $info)
                        <option value="{{ $id }}">{{ $info['name'] }} ({{ $info['total'] }} course{{ $info['total'] === 1 ? '' : 's' }})</option>
                    @endforeach
                </select>

                <p class="text-[11px] text-slate-500 mt-2 leading-relaxed">
                    Every course belonging to the chosen university bumps its current marker by one,
                    and every enrolled student moves up to the next semester (or year, for boards),
                    clamped at the course's total duration so the graduating cohort doesn't overshoot.
                </p>

                <button type="submit"
                        class="mt-4 self-start inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    Upgrade University
                </button>
            </form>

            {{-- RESET form --}}
            <form method="POST" action="{{ route('master.reset.semester') }}"
                  onsubmit="return confirmAction(this, 'Snap every enrolled student of the chosen university to the picked semester / year per duration. This overrides whatever they had before.', 'Reset semester', { tone: 'amber', confirmLabel: 'Reset' });"
                  class="bg-gradient-to-br from-amber-50 via-white to-white border border-amber-100 rounded-xl p-5 flex flex-col"
                  data-reset-form>
                @csrf
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-500 text-white flex items-center justify-center">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582M20 20v-5h-.581M5.165 19.485A8.001 8.001 0 0019.418 15M18.836 4.515A8.001 8.001 0 004.582 9"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Reset to specific period</h3>
                        <p class="text-[11px] text-slate-500">Undo an accidental bump — pick what should currently run.</p>
                    </div>
                </div>

                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Select University / Board</label>
                <select name="university_id" required
                        onchange="document.querySelectorAll('[data-reset-group]').forEach(g => g.classList.toggle('hidden', g.dataset.uni !== this.value));"
                        class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-300/60 focus:border-amber-300/60 transition">
                    <option value="">— Pick a university or board —</option>
                    @foreach ($upgradeUnis as $id => $info)
                        <option value="{{ $id }}">{{ $info['name'] }}</option>
                    @endforeach
                </select>

                <div class="mt-3 space-y-3">
                    @foreach ($upgradeUnis as $id => $info)
                        <div data-reset-group data-uni="{{ $id }}" class="hidden">
                            @if (empty($info['by_duration']))
                                <p class="text-[11px] text-slate-500 italic">No courses to reset for {{ $info['name'] }}.</p>
                            @else
                                <p class="text-[11px] font-semibold text-slate-600 mb-1.5">
                                    {{ $info['name'] }} — select the current {{ $info['is_board'] ? 'year' : 'semester' }} per course duration:
                                </p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach ($info['by_duration'] as $durYears => $coursesInDuration)
                                        @php
                                            $periodsInDuration = $info['is_board'] ? $durYears : $durYears * 2;
                                            $unitLabel = $info['is_board'] ? 'Year' : 'Semester';
                                        @endphp
                                        <label class="block bg-slate-50 border border-slate-100 rounded-lg p-2.5">
                                            <span class="block text-[11px] font-semibold text-slate-700">
                                                {{ $durYears }}-year courses
                                                <span class="text-slate-400 font-normal">·
                                                    {{ count($coursesInDuration) }} course{{ count($coursesInDuration) === 1 ? '' : 's' }}
                                                </span>
                                            </span>
                                            <select name="targets[{{ $durYears }}]"
                                                    class="mt-1 w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-md text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-300/60 focus:border-amber-300/60 transition">
                                                <option value="">— Pick {{ strtolower($unitLabel) }} —</option>
                                                @for ($p = 1; $p <= $periodsInDuration; $p++)
                                                    <option value="{{ $p }}">{{ $unitLabel }} {{ $p }}</option>
                                                @endfor
                                            </select>
                                            <span class="block mt-1 text-[10px] text-slate-400 leading-snug">
                                                Will be applied to: {{ collect($coursesInDuration)->pluck('name')->take(3)->implode(', ') }}{{ count($coursesInDuration) > 3 ? '…' : '' }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <button type="submit"
                        class="mt-4 self-start inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582M20 20v-5h-.581M5.165 19.485A8.001 8.001 0 0019.418 15M18.836 4.515A8.001 8.001 0 004.582 9"/></svg>
                    Reset Selected
                </button>
            </form>
        </div>

        {{-- READ-ONLY rollup of per-course current period for context --}}
        @if ($upgradeRows->isEmpty())
            @include('master._empty', ['icon' => 'cap', 'title' => 'No courses yet', 'subtitle' => 'Add courses from the Courses tab — they will show up here for semester tracking.', 'action' => null])
        @else
            <div class="px-6 lg:px-8 pt-5 pb-2 flex items-center gap-2">
                <h4 class="text-sm font-bold text-slate-800">Current state · per course</h4>
                <span class="text-[11px] text-slate-400">Reference only — use the forms above to change.</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-[11px] font-semibold tracking-wider uppercase text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-6 py-3">Course</th>
                            <th class="text-left px-6 py-3">University / Board</th>
                            <th class="text-left px-6 py-3">Currently Running</th>
                            <th class="text-left px-6 py-3">Students by Period</th>
                            <th class="text-right px-6 py-3">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($upgradeRows as $row)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-3">
                                    <div class="font-medium text-slate-800">{{ $row['name'] }}</div>
                                    <div class="text-xs text-slate-500">
                                        {{ $row['periods'] }} {{ \Illuminate\Support\Str::plural(strtolower($row['period_label']), $row['periods']) }} total
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-slate-600">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $row['university'] ?: '—' }}</span>
                                        @if ($row['is_board'])
                                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700">Board</span>
                                        @else
                                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-pink-50 text-pink-700">University</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-pink-50 text-pink-700 text-xs font-bold">
                                        {{ $row['period_label'] }} {{ $row['current_semester'] }}
                                        <span class="text-pink-400 font-normal">/ {{ $row['periods'] }}</span>
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex flex-wrap items-center gap-1">
                                        @foreach ($row['buckets'] as $period => $count)
                                            @php $isCurrent = $period === $row['current_semester']; @endphp
                                            <span title="{{ $row['period_label'] }} {{ $period }} — {{ $count }} student(s)"
                                                  class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold
                                                         {{ $isCurrent ? 'bg-pink-600 text-white' : ($count > 0 ? 'bg-slate-100 text-slate-700' : 'bg-slate-50 text-slate-400') }}">
                                                {{ $row['period_short'] }}{{ $period }}
                                                <span class="opacity-80">·</span>
                                                <span>{{ $count }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-right text-slate-700 font-semibold">{{ $row['student_total'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</div>

@endsection

@section('slide-panel')
@include('master._panel', [
    'isAdmin'          => $isAdmin,
    'allUniversities'  => $allUniversities,
    'allCoursesData'   => $allCoursesData,
])

<script>
    window.UNIVERSITIES_DATA = @json($universitiesData);
    window.COURSES_DATA      = @json($coursesData);
    window.FEES_DATA         = @json($feesData);
    window.ALL_COURSES       = @json($allCoursesData);
    window.IS_ADMIN          = @json($isAdmin);

    const MasterPanel = (function () {
        const panel    = document.getElementById('masterPanel');
        const card     = document.getElementById('masterPanelCard');
        const backdrop = document.getElementById('masterPanelBackdrop');
        const modes    = document.querySelectorAll('.master-mode');

        function show(modeId) {
            modes.forEach(m => m.classList.toggle('hidden', m.id !== modeId));
            panel.classList.remove('hidden');
            panel.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(() => {
                backdrop.classList.add('opacity-100');
                backdrop.classList.remove('opacity-0');
                card.classList.remove('translate-x-full');
            });
        }
        function close() {
            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
            card.classList.add('translate-x-full');
            setTimeout(() => {
                panel.classList.add('hidden');
                panel.setAttribute('aria-hidden', 'true');
            }, 250);
        }

        // ────── University ──────
        function fillUniversityView(u) {
            document.getElementById('viewUniversityName').textContent     = u.name;
            document.getElementById('viewUniversityAddress').textContent  = u.address || '—';
            document.getElementById('viewUniversityWebsite').textContent  = u.website || '—';
            document.getElementById('viewUniversityFee').textContent      = '₹' + Number(u.registration_fee || 0).toLocaleString('en-IN');
            const typeBadge = document.getElementById('viewUniversityType');
            typeBadge.textContent = u.type === 'board' ? 'Board' : 'University';
            typeBadge.className = 'inline-block text-[10px] font-semibold px-1.5 py-0.5 rounded ' +
                (u.type === 'board' ? 'bg-emerald-50 text-emerald-700' : 'bg-pink-50 text-pink-700');
            const img = document.getElementById('viewUniversityImage');
            const init = document.getElementById('viewUniversityInitial');
            if (u.image_url) {
                img.src = u.image_url;
                img.classList.remove('hidden');
                init.classList.add('hidden');
            } else {
                img.classList.add('hidden');
                init.classList.remove('hidden');
                init.textContent = (u.name || '?').charAt(0).toUpperCase();
            }
            if (window.IS_ADMIN) {
                document.getElementById('viewUniversityEdit').onclick = () => openEdit('university', u.id);
                document.getElementById('viewUniversityDeleteForm').action = @json(url('/master-data/universities/__ID__')).replace('__ID__', u.id);
            }
        }
        function fillUniversityForm(formId, u) {
            const f = document.getElementById(formId);
            f.querySelector('[name="name"]').value             = u?.name || '';
            f.querySelector('[name="address"]').value          = u?.address || '';
            f.querySelector('[name="website"]').value          = u?.website || '';
            f.querySelector('[name="registration_fee"]').value = u?.registration_fee ?? '';
            f.querySelectorAll('[name="type"]').forEach(r => r.checked = (r.value === (u?.type || 'university')));
            const preview = f.querySelector('[data-image-preview]');
            if (preview) {
                if (u?.image_url) { preview.src = u.image_url; preview.classList.remove('hidden'); }
                else preview.classList.add('hidden');
            }
            const fileLabel = f.querySelector('[data-file-name]');
            if (fileLabel) fileLabel.textContent = '';
        }

        // ────── Course ──────
        function fillCourseView(c) {
            document.getElementById('viewCourseName').textContent       = c.name;
            document.getElementById('viewCourseUniversity').textContent = c.university || '—';
            document.getElementById('viewCourseMode').textContent       = c.mode || '—';
            const durText = c.is_board
                ? (c.duration_years || 0) + ' yrs'
                : (c.duration_years || 0) + ' yrs · ' + (c.semesters || 0) + ' sem';
            document.getElementById('viewCourseDuration').textContent   = durText;
            document.getElementById('viewCourseLateral').textContent    = c.lateral_entry ? 'Yes' : 'No';
            document.getElementById('viewCourseSubjects').textContent   = c.subjects || '—';
            if (window.IS_ADMIN) {
                document.getElementById('viewCourseEdit').onclick = () => openEdit('course', c.id);
                document.getElementById('viewCourseDeleteForm').action = @json(url('/master-data/courses/__ID__')).replace('__ID__', c.id);
            }
        }
        function fillCourseForm(formId, c) {
            const f = document.getElementById(formId);
            f.querySelector('[name="university_id"]').value    = c?.university_id || '';
            f.querySelector('[name="name"]').value             = c?.name || '';
            f.querySelector('[name="mode"]').value             = c?.mode || '';
            f.querySelector('[name="duration_years"]').value   = c?.duration_years || '';
            f.querySelector('[name="registration_fee"]').value = c?.registration_fee ?? '';
            f.querySelector('[name="fee_per_sem"]').value      = c?.fee_per_sem ?? '';
            f.querySelector('[name="lateral_entry"]').checked  = !!c?.lateral_entry;
            f.querySelector('[name="subjects"]').value         = c?.subjects || '';
            // Sync the fee label (Semester / Annual) with the selected university type.
            syncCourseFeeLabel(f);
        }

        // Boards charge per year, universities per semester. Reflect that in
        // the course form's fee field label so the admin knows what they're
        // entering.
        function syncCourseFeeLabel(form) {
            const uniSel    = form.querySelector('[name="university_id"]');
            const opt       = uniSel?.selectedOptions?.[0];
            const isBoard   = opt?.dataset?.type === 'board';
            const label     = form.querySelector('[data-fee-label]');
            const input     = form.querySelector('[name="fee_per_sem"]');
            if (label) label.textContent = isBoard ? 'Annual Fee (₹)' : 'Semester Fee (₹)';
            if (input) input.placeholder = isBoard ? 'e.g. 25000 per year' : 'e.g. 25000 per semester';
        }

        // ────── Fee ──────
        function recomputeFeeTotal(form) {
            const courseId = parseInt(form.querySelector('[name="course_id"]').value || 0, 10);
            const course   = window.ALL_COURSES.find(c => c.id === courseId);
            const isBoard  = !!course?.is_board;
            const periods  = course?.fee_period_count || (isBoard ? Math.ceil(course?.duration_years || 0) : (course?.semesters || 0));
            const regFee   = course?.registration_fee || 0;
            const perFee   = course?.fee_per_sem || 0;
            const total    = course?.total_fee ?? (perFee * periods + regFee);
            const label    = course?.fee_period_label || (isBoard ? 'Annual' : 'Semester');
            const fmt = v => '₹' + Number(v).toLocaleString('en-IN');
            const r = form.querySelector('[data-reg-fee]');     if (r) r.textContent = fmt(regFee);
            const p = form.querySelector('[data-per-sem]');     if (p) p.textContent = fmt(perFee);
            const s = form.querySelector('[data-semesters]');   if (s) s.textContent = periods;
            const t = form.querySelector('[data-total-fee]');   if (t) t.textContent = fmt(total);
            form.querySelectorAll('[data-period-label]').forEach(el => {
                el.textContent = label === 'Annual' ? 'Fee per year' : 'Fee per semester';
            });
            form.querySelectorAll('[data-period-count-label]').forEach(el => {
                el.textContent = label === 'Annual' ? 'Years (from duration)' : 'Semesters (from duration)';
            });
        }
        function rebuildFeeCourseSelect(form, universityId, selectedCourseId) {
            const sel = form.querySelector('[name="course_id"]');
            const uniId = parseInt(universityId || 0, 10);
            sel.innerHTML = '<option value="">Select course</option>';
            window.ALL_COURSES
                .filter(c => !uniId || c.university_id === uniId)
                .forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name + ' · ' + c.duration_years + ' yrs';
                    if (selectedCourseId && c.id === selectedCourseId) opt.selected = true;
                    sel.appendChild(opt);
                });
            recomputeFeeTotal(form);
        }
        function fillFeeView(f) {
            const isBoard = !!f.is_board;
            const periods = f.fee_period_count || (isBoard ? Math.ceil(f.duration_years || 0) : (f.semesters || 0));
            const regFee  = Number(f.registration_fee || 0);
            const perFee  = Number(f.fee_per_sem || 0);
            const total   = Number(f.total_fee ?? (perFee * periods + regFee));
            const fmt = v => '₹' + v.toLocaleString('en-IN');

            document.getElementById('viewFeeUniversity').textContent = f.university || '—';
            document.getElementById('viewFeeCourse').textContent     = f.course || '—';
            const durText = isBoard
                ? (f.duration_years || 0) + ' yrs'
                : (f.duration_years || 0) + ' yrs · ' + (f.semesters || 0) + ' sem';
            document.getElementById('viewFeeDuration').textContent   = durText;
            document.getElementById('viewFeeRegistration').textContent = fmt(regFee);
            document.getElementById('viewFeePerSem').textContent     = fmt(perFee);
            document.getElementById('viewFeeTotal').textContent      = fmt(total);
            const perLabel = document.getElementById('viewFeePerSemLabel');
            if (perLabel) perLabel.textContent = isBoard ? 'Fee per year' : 'Fee per semester';
            if (window.IS_ADMIN) {
                document.getElementById('viewFeeEdit').onclick = () => openEdit('fee', f.id);
                document.getElementById('viewFeeDeleteForm').action = @json(url('/master-data/fees/__ID__')).replace('__ID__', f.id);
            }
        }
        function fillFeeForm(formId, f) {
            const form = document.getElementById(formId);
            const uniSel = form.querySelector('[name="university_id_picker"]');
            uniSel.value = f?.university_id || '';
            rebuildFeeCourseSelect(form, uniSel.value, f?.course_id);
            recomputeFeeTotal(form);
        }

        function openCreate(entity) {
            if (entity === 'university') {
                fillUniversityForm('formUniversityCreate', null);
                show('formUniversityCreate');
            } else if (entity === 'course') {
                fillCourseForm('formCourseCreate', null);
                show('formCourseCreate');
            } else if (entity === 'fee') {
                fillFeeForm('formFeeCreate', null);
                show('formFeeCreate');
            }
        }

        function openEdit(entity, id) {
            if (entity === 'university') {
                const u = window.UNIVERSITIES_DATA[id]; if (!u) return;
                const f = document.getElementById('formUniversityEdit');
                f.action = @json(url('/master-data/universities/__ID__')).replace('__ID__', id);
                fillUniversityForm('formUniversityEdit', u);
                show('formUniversityEdit');
            } else if (entity === 'course') {
                const c = window.COURSES_DATA[id]; if (!c) return;
                const f = document.getElementById('formCourseEdit');
                f.action = @json(url('/master-data/courses/__ID__')).replace('__ID__', id);
                fillCourseForm('formCourseEdit', c);
                show('formCourseEdit');
            } else if (entity === 'fee') {
                const fee = window.FEES_DATA[id]; if (!fee) return;
                const f = document.getElementById('formFeeEdit');
                f.action = @json(url('/master-data/fees/__ID__')).replace('__ID__', id);
                fillFeeForm('formFeeEdit', fee);
                show('formFeeEdit');
            }
        }

        function openView(entity, id) {
            if (entity === 'university') {
                const u = window.UNIVERSITIES_DATA[id]; if (!u) return;
                fillUniversityView(u);
                show('viewUniversity');
            } else if (entity === 'course') {
                const c = window.COURSES_DATA[id]; if (!c) return;
                fillCourseView(c);
                show('viewCourse');
            } else if (entity === 'fee') {
                const fee = window.FEES_DATA[id]; if (!fee) return;
                fillFeeView(fee);
                show('viewFee');
            }
        }

        return { openCreate, openEdit, openView, close, rebuildFeeCourseSelect, recomputeFeeTotal, syncCourseFeeLabel };
    })();

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !document.getElementById('masterPanel').classList.contains('hidden')) {
            MasterPanel.close();
        }
    });

    // Wire fee-form live recompute and university->course dependency.
    document.querySelectorAll('.fee-form').forEach(form => {
        const uniSel = form.querySelector('[name="university_id_picker"]');
        uniSel?.addEventListener('change', () => MasterPanel.rebuildFeeCourseSelect(form, uniSel.value, null));
        form.querySelector('[name="course_id"]')?.addEventListener('change', () => MasterPanel.recomputeFeeTotal(form));
    });

    // Course forms: switch the Semester/Annual fee label when the
    // selected university type changes.
    document.querySelectorAll('#formCourseCreate, #formCourseEdit').forEach(form => {
        const uniSel = form.querySelector('[name="university_id"]');
        uniSel?.addEventListener('change', () => MasterPanel.syncCourseFeeLabel(form));
    });

    // File-name echo for image uploads.
    document.querySelectorAll('[data-image-input]').forEach(input => {
        input.addEventListener('change', () => {
            const form = input.closest('form');
            const label = form.querySelector('[data-file-name]');
            if (label) label.textContent = input.files[0]?.name || '';
            const preview = form.querySelector('[data-image-preview]');
            if (preview && input.files.length) {
                preview.src = URL.createObjectURL(input.files[0]);
                preview.classList.remove('hidden');
            }
        });
    });
</script>
@endsection
