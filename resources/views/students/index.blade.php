@extends('layouts.admin')

@section('title', 'Students - SSB Education')

@php
    $studentsData = $students->map(function ($s) {
        $docs = [];
        foreach (\App\Models\Student::DOCUMENT_FIELDS as $field) {
            $docs[$field] = $s->documentUrl($field);
        }
        return [
            'id'                    => $s->id,
            'name'                  => $s->name,
            'mobile'                => $s->mobile,
            'email'                 => $s->email,
            'admission_no'          => $s->admission_no,
            'class_name'            => $s->class_name,
            'university_id'         => $s->university_id,
            'university_name'       => $s->university?->name,
            'university_type'       => $s->university?->type,
            'course_id'             => $s->course_id,
            'course_name'           => $s->course?->name,
            'mode'                  => $s->mode,
            'enrollment_type'       => $s->enrollment_type,
            'course_year'           => $s->course_year,
            'semester'              => $s->semester,
            'father_name'           => $s->father_name,
            'mother_name'           => $s->mother_name,
            'parent_name'           => $s->parent_name,
            'gender'                => $s->gender,
            'dob'                   => $s->dob?->format('Y-m-d'),
            'category'              => $s->category,
            'nationality'           => $s->nationality,
            'religion'              => $s->religion,
            'aadhar_number'         => $s->aadhar_number,
            'address'               => $s->address,
            'country'               => $s->country,
            'state'                 => $s->state,
            'city'                  => $s->city,
            'pincode'               => $s->pincode,
            'academic_records'      => $s->academic_records ?? [],
            'documents'             => $docs,
            'active'                => (bool) $s->active,
            'creator_name'          => $s->creator?->name,
            'created_at'            => $s->created_at?->format('d M Y'),
        ];
    })->keyBy('id');

    $statusChips = [
        'all'      => 'All',
        'active'   => 'Active',
        'inactive' => 'Inactive',
    ];

    $isAdmin = auth()->user()->isAdmin();

    $buildUrl = function (array $overrides) use ($status, $search, $universityId, $courseId, $createdBy) {
        $params = array_merge([
            'status'        => $status === 'all' ? null : $status,
            'q'             => $search !== '' ? $search : null,
            'university_id' => $universityId,
            'course_id'     => $courseId,
            'created_by'    => $createdBy,
        ], $overrides);
        $params = array_filter($params, fn ($v) => $v !== null && $v !== '' && $v !== 0);
        return route('students.index').($params ? '?'.http_build_query($params) : '');
    };

    $exportParams = array_filter([
        'status'        => $status === 'all' ? null : $status,
        'q'             => $search !== '' ? $search : null,
        'university_id' => $universityId,
        'course_id'     => $courseId,
        'created_by'    => $createdBy,
    ], fn ($v) => $v !== null && $v !== '' && $v !== 0);
    $exportUrl = route('students.export').($exportParams ? '?'.http_build_query($exportParams) : '');
@endphp

@section('admin-header')
<div class="sticky top-0 z-20 bg-white border-b border-slate-200">
    {{-- Title + stats + actions --}}
    <div class="px-6 lg:px-10 py-3 flex flex-wrap items-center gap-x-6 gap-y-2 border-b border-slate-100">
        <div class="mr-auto">
            <h2 class="text-base font-bold text-slate-800">Students</h2>
            <p class="text-xs text-slate-500 mt-0.5">Manage student records and admissions</p>
        </div>

        <div class="flex items-center gap-x-6 gap-y-1 text-xs text-slate-500 flex-wrap">
            <span>Total: <span class="text-slate-800 font-semibold ml-1">{{ $stats['total'] }}</span></span>
            <span>Active: <span class="text-emerald-600 font-semibold ml-1">{{ $stats['active'] }}</span></span>
            <span>Inactive: <span class="text-amber-600 font-semibold ml-1">{{ $stats['inactive'] }}</span></span>
        </div>

        <a href="{{ $exportUrl }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-semibold transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
            </svg>
            Export
        </a>

        <button type="button" onclick="StudentsPanel.openCreate()"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Student
        </button>
    </div>

    {{-- Filter row — single GET form. Plain native selects with a
         dedicated label cell above each one so they line up cleanly,
         read well on mobile (where the row wraps) and don't fight the
         browser's native chevron. The status chips below are anchors
         that preserve the rest of the filter state via $buildUrl().
         --}}
    @php
        $unisOnly   = $allUniversities->where('type', \App\Models\University::TYPE_UNIVERSITY);
        $boardsOnly = $allUniversities->where('type', \App\Models\University::TYPE_BOARD);
        $hasFilters = $universityId || $courseId || $createdBy || $search !== '' || $status !== 'all';

        $selectClasses = 'w-full px-2.5 h-8 bg-white border border-slate-200 rounded-lg text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 transition shadow-sm';
    @endphp
    <form method="GET" action="{{ route('students.index') }}"
          class="px-4 sm:px-6 lg:px-10 py-3 bg-slate-50/70 border-t border-slate-100">

        {{-- Keep the current status when a dropdown auto-submits, so
             chip selection doesn't get clobbered. --}}
        @if ($status !== 'all')
            <input type="hidden" name="status" value="{{ $status }}">
        @endif

        <div class="grid grid-cols-2 sm:grid-cols-3 {{ $isAdmin ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }} gap-x-3 gap-y-2 items-end">

            {{-- University / Board --}}
            <label class="block">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">University / Board</span>
                <select name="university_id" data-filter-uni
                        onchange="this.form.submit()" class="{{ $selectClasses }}">
                    <option value="">All</option>
                    @if ($unisOnly->isNotEmpty())
                        <optgroup label="Universities">
                            @foreach ($unisOnly as $u)
                                <option value="{{ $u->id }}" @selected($universityId === $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if ($boardsOnly->isNotEmpty())
                        <optgroup label="Boards">
                            @foreach ($boardsOnly as $u)
                                <option value="{{ $u->id }}" @selected($universityId === $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </label>

            {{-- Course (filters cascade off the picked university) --}}
            <label class="block">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Course</span>
                <select name="course_id" data-filter-course
                        onchange="this.form.submit()" class="{{ $selectClasses }}">
                    <option value="">All</option>
                    @foreach ($allCourses as $c)
                        <option value="{{ $c->id }}" data-university="{{ $c->university_id }}" @selected($courseId === $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </label>

            @if ($isAdmin)
                <label class="block">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Added By</span>
                    <select name="created_by"
                            onchange="this.form.submit()" class="{{ $selectClasses }}">
                        <option value="">All</option>
                        <option value="self" @selected($createdBy === (int) auth()->id())>Self</option>
                        @foreach ($userOptions as $u)
                            @continue($u->id === auth()->id())
                            <option value="{{ $u->id }}" @selected($createdBy === $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </label>
            @endif

            {{-- Search --}}
            <label class="block col-span-2 sm:col-span-1">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Search</span>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                    </div>
                    <input type="text" name="q" value="{{ $search }}"
                           placeholder="Name, mobile, admission…"
                           class="w-full pl-7 pr-3 h-8 bg-white border border-slate-200 rounded-lg text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 transition shadow-sm">
                </div>
            </label>

            {{-- Submit + Clear (same row baseline as the search field) --}}
            <div class="flex items-center gap-2 col-span-2 sm:col-span-1">
                <button type="submit"
                        class="h-8 px-3.5 rounded-lg text-xs font-semibold bg-pink-600 hover:bg-pink-700 text-white transition shadow-sm">
                    Search
                </button>
                @if ($hasFilters)
                    <a href="{{ route('students.index') }}"
                       class="h-8 inline-flex items-center px-2.5 rounded-lg text-xs font-semibold text-slate-500 hover:bg-slate-200/70 transition">
                        Clear
                    </a>
                @endif
            </div>
        </div>

        {{-- Status chips on their own row so they don't fight the
             dropdown widths and stay tappable on mobile. --}}
        <div class="mt-3 flex items-center gap-2 flex-wrap">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Status</span>
            <div class="inline-flex items-center gap-1 bg-white border border-slate-200 rounded-lg p-0.5 h-8 shadow-sm">
                @foreach ($statusChips as $key => $label)
                    @php $isActive = $status === $key; @endphp
                    <a href="{{ $buildUrl(['status' => $key === 'all' ? null : $key]) }}"
                       class="px-2.5 h-7 inline-flex items-center rounded-md text-xs font-semibold transition
                              {{ $isActive
                                    ? 'bg-pink-600 text-white shadow-sm shadow-pink-500/30'
                                    : 'text-slate-600 hover:bg-slate-100' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            @if ($hasFilters)
                <span class="text-[11px] text-slate-400 ml-2">
                    Showing <strong class="text-slate-700">{{ $students->count() }}</strong> result(s)
                </span>
            @endif
        </div>
    </form>
</div>

<script>
    // Cascade: course options should narrow to the picked university.
    (function () {
        const uniSel    = document.querySelector('[data-filter-uni]');
        const courseSel = document.querySelector('[data-filter-course]');
        if (!uniSel || !courseSel) return;
        function apply() {
            const uni = uniSel.value;
            const selected = courseSel.value;
            courseSel.querySelectorAll('option').forEach(opt => {
                if (!opt.value) { opt.hidden = false; return; }
                const ok = !uni || opt.dataset.university === uni;
                opt.hidden = !ok;
                if (!ok && opt.value === selected) courseSel.value = '';
            });
        }
        uniSel.addEventListener('change', apply);
        apply();
    })();
</script>
@endsection

@section('admin')
{{-- LISTING — compact 5-column table (4 for sub-admin without "Added By").
     Course cell carries year/sem chips inline so we don't burn a whole
     column on two characters. The status chip and every per-row action
     live together on the right and stop click-propagation so they don't
     also open the View panel underneath. --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-[11px] font-semibold tracking-wider uppercase text-slate-500 border-b border-slate-200 bg-slate-50/60">
                <tr>
                    <th class="text-left px-4 sm:px-6 py-3 font-semibold">Student</th>
                    <th class="text-left px-4 sm:px-6 py-3 font-semibold hidden sm:table-cell">Mobile</th>
                    <th class="text-left px-4 sm:px-6 py-3 font-semibold">Course · Year / Sem</th>
                    @if ($isAdmin)
                        <th class="text-left px-4 sm:px-6 py-3 font-semibold hidden md:table-cell">Added By</th>
                    @endif
                    <th class="text-right px-4 sm:px-6 py-3 font-semibold">Status &amp; Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($students as $s)
                    @php
                        $isBoard = $s->university?->type === \App\Models\University::TYPE_BOARD;
                        $formUrl     = route('students.form', $s);
                        $downloadUrl = route('students.form', ['student' => $s->id, 'download' => 1]);
                    @endphp
                    <tr class="student-row hover:bg-slate-50 transition cursor-pointer"
                        data-student-id="{{ $s->id }}"
                        onclick="StudentsPanel.openView({{ $s->id }})">

                        {{-- Student: avatar + name + father (and mobile on xs since the column is hidden) --}}
                        <td class="px-4 sm:px-6 py-3">
                            <div class="flex items-center gap-3">
                                @if ($photoUrl = $s->documentUrl('photo_path'))
                                    <img src="{{ $photoUrl }}" alt="" class="w-9 h-9 rounded-full object-cover shrink-0">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-pink-50 text-pink-600 font-bold text-sm flex items-center justify-center shrink-0">
                                        {{ strtoupper(mb_substr($s->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-800 truncate">{{ $s->name }}</div>
                                    @if ($s->father_name || $s->parent_name)
                                        <div class="text-[11px] text-slate-500 truncate">
                                            S/O {{ $s->father_name ?: $s->parent_name }}
                                        </div>
                                    @endif
                                    {{-- Mobile-only mobile (column itself hides under sm) --}}
                                    <div class="text-[11px] text-slate-400 sm:hidden truncate">{{ $s->mobile }}</div>
                                </div>
                            </div>
                        </td>

                        {{-- Mobile column (sm+) --}}
                        <td class="px-4 sm:px-6 py-3 text-slate-600 hidden sm:table-cell whitespace-nowrap">{{ $s->mobile }}</td>

                        {{-- Course + university + year/sem chips inline --}}
                        <td class="px-4 sm:px-6 py-3">
                            <div class="text-slate-700 font-medium truncate max-w-[16rem]">{{ $s->course?->name ?: '—' }}</div>
                            <div class="flex items-center gap-1.5 flex-wrap mt-0.5">
                                @if ($s->university)
                                    <span class="text-[11px] text-slate-400 truncate max-w-[12rem]">{{ $s->university->name }}</span>
                                @endif
                                @if ($s->course_year)
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-semibold">Y{{ $s->course_year }}</span>
                                @endif
                                @if (! $isBoard && $s->semester)
                                    <span class="px-1.5 py-0.5 rounded bg-pink-50 text-pink-700 text-[10px] font-semibold">S{{ $s->semester }}</span>
                                @endif
                                @if ($isBoard)
                                    <span class="px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 text-[10px] font-semibold">Board</span>
                                @endif
                            </div>
                        </td>

                        {{-- Added By (admin, md+) --}}
                        @if ($isAdmin)
                            <td class="px-4 sm:px-6 py-3 text-slate-600 hidden md:table-cell whitespace-nowrap">
                                {{ $s->creator?->name ?: '—' }}
                            </td>
                        @endif

                        {{-- Status + actions cluster — all stop click-propagation
                             so tapping any button never accidentally opens the
                             View panel underneath. --}}
                        <td class="px-4 sm:px-6 py-3">
                            <div class="flex items-center justify-end gap-1.5" onclick="event.stopPropagation()">
                                @if ($s->active)
                                    <span class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                    <span class="sm:hidden w-2 h-2 rounded-full bg-emerald-500" title="Active"></span>
                                @else
                                    <span class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 text-[10px] font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Inactive
                                    </span>
                                    <span class="sm:hidden w-2 h-2 rounded-full bg-rose-500" title="Inactive"></span>
                                @endif

                                <a href="{{ $formUrl }}" target="_blank" rel="noopener" title="Open admission form"
                                   class="w-8 h-8 rounded-md text-slate-500 hover:bg-pink-50 hover:text-pink-600 inline-flex items-center justify-center transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </a>
                                <a href="{{ $downloadUrl }}" title="Download admission form"
                                   class="w-8 h-8 rounded-md text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 inline-flex items-center justify-center transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                                </a>
                                <button type="button" onclick="StudentsPanel.openEdit({{ $s->id }})"
                                        title="Edit"
                                        class="w-8 h-8 rounded-md text-slate-500 hover:bg-slate-100 hover:text-pink-600 inline-flex items-center justify-center transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('students.destroy', $s) }}"
                                      onsubmit="return confirmAction(this, 'Delete this student? This action cannot be undone.', 'Delete student');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Delete"
                                            class="w-8 h-8 rounded-md text-slate-500 hover:bg-rose-50 hover:text-rose-600 inline-flex items-center justify-center transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 5 : 4 }}" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                    </svg>
                                </div>
                                <p class="text-sm text-slate-500">No students found</p>
                                <p class="text-xs text-slate-400">
                                    Click <span class="font-semibold text-pink-600">Add Student</span> to create one.
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('slide-panel')
{{-- VIEW MODE: slide-in on the right (compact details + documents) --}}
<aside id="slidePanel" class="absolute inset-0 z-30 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/30 opacity-0 transition-opacity duration-200" id="slidePanelBackdrop" onclick="StudentsPanel.close()"></div>
    <div id="slidePanelCard"
         class="absolute right-0 top-0 bottom-0 w-full max-w-xl bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-out">

        <button type="button" onclick="StudentsPanel.close()" aria-label="Close"
                class="absolute top-3 right-3 z-10 w-8 h-8 rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700 inline-flex items-center justify-center transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div id="panelView" class="flex-1 flex flex-col min-h-0">
            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                <div class="flex flex-col items-center text-center pb-5 border-b border-slate-100">
                    <div class="w-20 h-20 rounded-full bg-pink-50 text-pink-600 font-bold text-2xl flex items-center justify-center overflow-hidden">
                        <img id="viewPhoto" src="" alt="" class="w-full h-full object-cover hidden">
                        <span id="viewInitial"></span>
                    </div>
                    <h4 id="viewName" class="mt-3 text-base font-bold text-slate-800"></h4>
                    <p id="viewMobile" class="text-sm text-slate-500 mt-0.5"></p>
                    <span id="viewStatus" class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium"></span>
                </div>

                <div>
                    <h5 class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Course Placement</h5>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-[11px] text-slate-500">University / Board</dt><dd id="viewUni" class="text-slate-800"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">Course</dt><dd id="viewCourse" class="text-slate-800"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">Mode</dt><dd id="viewModeF" class="text-slate-800 capitalize"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">Type</dt><dd id="viewTypeF" class="text-slate-800 capitalize"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">Year</dt><dd id="viewYear" class="text-slate-800"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">Semester</dt><dd id="viewSem" class="text-slate-800"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">Admission No</dt><dd id="viewAdmission" class="text-slate-800"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">Added By</dt><dd id="viewCreator" class="text-slate-800"></dd></div>
                    </dl>
                </div>

                <div>
                    <h5 class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Personal</h5>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-[11px] text-slate-500">Father</dt><dd id="viewFather" class="text-slate-800"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">Mother</dt><dd id="viewMother" class="text-slate-800"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">Gender</dt><dd id="viewGender" class="text-slate-800 capitalize"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">DOB</dt><dd id="viewDob" class="text-slate-800"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">Category</dt><dd id="viewCategory" class="text-slate-800 uppercase"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">Nationality</dt><dd id="viewNationality" class="text-slate-800"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">Religion</dt><dd id="viewReligion" class="text-slate-800"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">Aadhar</dt><dd id="viewAadhar" class="text-slate-800"></dd></div>
                        <div class="col-span-2"><dt class="text-[11px] text-slate-500">Email</dt><dd id="viewEmail" class="text-slate-800 break-words"></dd></div>
                    </dl>
                </div>

                <div>
                    <h5 class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Address</h5>
                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div class="col-span-2"><dt class="text-[11px] text-slate-500">Address</dt><dd id="viewAddress" class="text-slate-800 whitespace-pre-line"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">City</dt><dd id="viewCity" class="text-slate-800"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">State</dt><dd id="viewState" class="text-slate-800"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">Country</dt><dd id="viewCountry" class="text-slate-800"></dd></div>
                        <div><dt class="text-[11px] text-slate-500">Pincode</dt><dd id="viewPincode" class="text-slate-800"></dd></div>
                    </dl>
                </div>

                <div>
                    <h5 class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Documents</h5>
                    <div id="viewDocs" class="grid grid-cols-2 gap-2 text-xs"></div>
                </div>

                <div>
                    <h5 class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Academic History</h5>
                    <div id="viewAcademic" class="overflow-x-auto"></div>
                </div>
            </div>

            <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                <form id="viewDeleteForm" method="POST" action=""
                      onsubmit="return confirmAction(this, 'Delete this student? This action cannot be undone.', 'Delete student');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 text-sm font-semibold text-rose-600 hover:text-rose-700 transition">Delete</button>
                </form>
                <button type="button" id="viewEditBtn"
                        class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Edit Student</button>
            </div>
        </div>
    </div>
</aside>

{{-- FULL-SCREEN CREATE / EDIT FORM
     Lives at the page root (z-50, fixed inset-0) so the sidebar and
     topbar both stay behind it — the admission form is a heavy form
     and benefits from the entire viewport. --}}
<div id="studentFormShell" class="hidden fixed inset-0 z-50 bg-slate-50">
    <form id="studentForm" method="POST" action="" enctype="multipart/form-data"
          class="h-full flex flex-col"
          onsubmit="StudentsPanel.beforeSubmit(this)">
        @csrf
        <input type="hidden" name="_method" id="studentFormMethod" value="POST">

        {{-- header --}}
        <div class="shrink-0 bg-white border-b border-slate-200 px-6 lg:px-10 py-3 flex items-center gap-3">
            <button type="button" onclick="StudentsPanel.closeForm()" aria-label="Close"
                    class="w-9 h-9 rounded-md text-slate-500 hover:bg-slate-100 hover:text-slate-700 inline-flex items-center justify-center transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="mr-auto">
                <h2 id="studentFormTitle" class="text-base font-bold text-slate-800">New Student Admission</h2>
                <p class="text-xs text-slate-500 mt-0.5">Fill the form below — all uploads are optional and can be added later.</p>
            </div>
            <button type="button" onclick="StudentsPanel.closeForm()"
                    class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
            <button type="submit"
                    class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                <span id="studentSubmitLabel">Save Student</span>
            </button>
        </div>

        {{-- body --}}
        <div class="flex-1 overflow-y-auto">
            <div class="max-w-7xl mx-auto px-6 lg:px-10 py-6 space-y-5">
                @include('students._fields', ['mode' => 'create'])
            </div>
        </div>
    </form>
</div>

<script>
    window.STUDENTS_DATA = @json($studentsData);
    window.STUDENT_UPDATE_URL_TEMPLATE = @json(url('/students/__ID__'));
    window.STUDENT_DESTROY_URL_TEMPLATE = @json(url('/students/__ID__'));
    window.STUDENT_STORE_URL = @json(route('students.store'));

    const StudentsPanel = (function () {
        const slide    = document.getElementById('slidePanel');
        const slideCrd = document.getElementById('slidePanelCard');
        const slideBd  = document.getElementById('slidePanelBackdrop');
        const formShell = document.getElementById('studentFormShell');
        const form     = document.getElementById('studentForm');

        function openSlide() {
            slide.classList.remove('hidden');
            slide.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(() => {
                slideBd.classList.add('opacity-100');
                slideBd.classList.remove('opacity-0');
                slideCrd.classList.remove('translate-x-full');
            });
        }
        function closeSlide() {
            slideBd.classList.remove('opacity-100');
            slideBd.classList.add('opacity-0');
            slideCrd.classList.add('translate-x-full');
            setTimeout(() => {
                slide.classList.add('hidden');
                slide.setAttribute('aria-hidden', 'true');
            }, 250);
        }

        function openForm() {
            formShell.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeForm() {
            formShell.classList.add('hidden');
            document.body.style.overflow = '';
        }
        function close() { closeSlide(); closeForm(); }

        function setText(id, val) { const el = document.getElementById(id); if (el) el.textContent = val || '—'; }

        function fillView(s) {
            setText('viewName',        s.name);
            setText('viewMobile',      s.mobile);
            setText('viewAdmission',   s.admission_no);
            setText('viewCreator',     s.creator_name);
            setText('viewUni',         s.university_name);
            setText('viewCourse',      s.course_name);
            setText('viewModeF',       s.mode);
            setText('viewTypeF',       s.enrollment_type);
            setText('viewYear',        s.course_year);
            setText('viewSem',         s.university_type === 'board' ? null : s.semester);
            setText('viewFather',      s.father_name || s.parent_name);
            setText('viewMother',      s.mother_name);
            setText('viewGender',      s.gender);
            setText('viewDob',         s.dob);
            setText('viewCategory',    s.category);
            setText('viewNationality', s.nationality);
            setText('viewReligion',    s.religion);
            setText('viewAadhar',      s.aadhar_number);
            setText('viewEmail',       s.email);
            setText('viewAddress',     s.address);
            setText('viewCity',        s.city);
            setText('viewState',       s.state);
            setText('viewCountry',     s.country);
            setText('viewPincode',     s.pincode);

            const photoImg = document.getElementById('viewPhoto');
            const initial  = document.getElementById('viewInitial');
            const photo    = s.documents && s.documents.photo_path;
            if (photo) {
                photoImg.src = photo; photoImg.classList.remove('hidden');
                initial.textContent = '';
            } else {
                photoImg.classList.add('hidden');
                initial.textContent = (s.name || '?').charAt(0).toUpperCase();
            }

            const status = document.getElementById('viewStatus');
            if (s.active) {
                status.className = 'mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700';
                status.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active';
            } else {
                status.className = 'mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-amber-700';
                status.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Inactive';
            }

            const docLabels = {
                photo_path: 'Photo', student_sign_path: 'Sign',
                aadhar_front_path: 'Aadhar Front', aadhar_back_path: 'Aadhar Back',
                marksheet_x_path: 'X Marksheet', marksheet_xii_path: 'XII Marksheet',
                marksheet_graduation_path: 'Graduation Marksheet',
                abc_id_path: 'ABC ID', deb_id_path: 'DEB ID', other_doc_path: 'Other Doc',
            };
            const docsBox = document.getElementById('viewDocs');
            docsBox.innerHTML = '';
            Object.keys(docLabels).forEach(k => {
                const url = s.documents && s.documents[k];
                const cell = document.createElement('div');
                cell.className = 'p-2 rounded-md border border-slate-100 ' + (url ? 'bg-emerald-50' : 'bg-slate-50');
                cell.innerHTML = url
                    ? '<div class="text-[11px] text-slate-500">' + docLabels[k] + '</div><a href="' + url + '" target="_blank" class="text-pink-600 font-semibold text-xs hover:underline">View</a>'
                    : '<div class="text-[11px] text-slate-500">' + docLabels[k] + '</div><span class="text-slate-400 text-xs">—</span>';
                docsBox.appendChild(cell);
            });

            const acad = document.getElementById('viewAcademic');
            if (!s.academic_records || s.academic_records.length === 0) {
                acad.innerHTML = '<p class="text-xs text-slate-400">No academic records on file.</p>';
            } else {
                let html = '<table class="w-full text-xs"><thead class="text-slate-500"><tr>'
                    + '<th class="text-left py-1 pr-2">Exam</th><th class="text-left py-1 px-2">Board / Univ</th><th class="text-left py-1 px-2">Subject</th><th class="text-left py-1 px-2">Year</th><th class="text-left py-1 pl-2">Grade</th></tr></thead><tbody>';
                s.academic_records.forEach(r => {
                    html += '<tr class="border-t border-slate-100"><td class="py-1 pr-2 font-semibold text-slate-700">' + (r.level || '') + '</td>'
                        + '<td class="py-1 px-2">' + (r.board || '—') + '</td>'
                        + '<td class="py-1 px-2">' + (r.subject || '—') + '</td>'
                        + '<td class="py-1 px-2">' + (r.year || '—') + '</td>'
                        + '<td class="py-1 pl-2">' + (r.grade || '—') + '</td></tr>';
                });
                html += '</tbody></table>';
                acad.innerHTML = html;
            }

            document.getElementById('viewDeleteForm').action = window.STUDENT_DESTROY_URL_TEMPLATE.replace('__ID__', s.id);
            document.getElementById('viewEditBtn').onclick = () => StudentsPanel.openEdit(s.id);
        }

        function setValue(name, val) {
            const el = form.querySelector('[name="' + name + '"]');
            if (!el) return;
            if (el.type === 'checkbox') el.checked = !!val;
            else el.value = val == null ? '' : val;
        }

        function fillForm(s) {
            form.reset();
            if (!s) {
                setValue('active', true);
                applyCourseFilter();
                applyBoardSemester();
                resetUploadLabels();
                return;
            }

            setValue('name',            s.name);
            setValue('mobile',          s.mobile);
            setValue('email',           s.email);
            setValue('admission_no',    s.admission_no);
            setValue('class_name',      s.class_name);
            setValue('university_id',   s.university_id);
            applyCourseFilter();
            setValue('course_id',       s.course_id);
            setValue('mode',            s.mode);
            setValue('enrollment_type', s.enrollment_type);
            setValue('course_year',     s.course_year);
            setValue('semester',        s.semester);
            setValue('father_name',     s.father_name);
            setValue('mother_name',     s.mother_name);
            setValue('gender',          s.gender);
            setValue('dob',             s.dob);
            setValue('category',        s.category);
            setValue('nationality',     s.nationality);
            setValue('religion',        s.religion);
            setValue('aadhar_number',   s.aadhar_number);
            setValue('address',         s.address);
            setValue('country',         s.country);
            setValue('state',           s.state);
            setValue('city',            s.city);
            setValue('pincode',         s.pincode);
            setValue('active',          !!s.active);

            // Academic records — fill the 5 fixed rows in order, falling
            // back to empty when the level wasn't recorded.
            const levels = ['X', 'XII', 'UG', 'PG', 'OTHER'];
            const boardInputs = form.querySelectorAll('input[name="academic_board[]"]');
            const subjInputs  = form.querySelectorAll('input[name="academic_subject[]"]');
            const yearInputs  = form.querySelectorAll('input[name="academic_year[]"]');
            const gradeInputs = form.querySelectorAll('input[name="academic_grade[]"]');
            const map = {};
            (s.academic_records || []).forEach(r => { if (r && r.level) map[String(r.level).toUpperCase()] = r; });
            levels.forEach((lvl, i) => {
                const row = map[lvl] || {};
                if (boardInputs[i]) boardInputs[i].value = row.board || '';
                if (subjInputs[i])  subjInputs[i].value  = row.subject || '';
                if (yearInputs[i])  yearInputs[i].value  = row.year || '';
                if (gradeInputs[i]) gradeInputs[i].value = row.grade || '';
            });

            applyBoardSemester();
            resetUploadLabels(s.documents);
        }

        function applyCourseFilter() {
            const uniSel = form.querySelector('[data-student-uni]');
            const courseSel = form.querySelector('[data-student-course]');
            if (!uniSel || !courseSel) return;
            const uni = uniSel.value;
            const selected = courseSel.value;
            courseSel.querySelectorAll('option').forEach(opt => {
                if (!opt.value) { opt.hidden = false; return; }
                const ok = !uni || opt.dataset.university === uni;
                opt.hidden = !ok;
                if (!ok && opt.value === selected) courseSel.value = '';
            });
        }

        function applyBoardSemester() {
            const uniSel = form.querySelector('[data-student-uni]');
            const wrap   = form.querySelector('[data-semester-wrap]');
            const sem    = form.querySelector('[name="semester"]');
            if (!uniSel || !wrap || !sem) return;
            const opt = uniSel.options[uniSel.selectedIndex];
            const isBoard = opt && opt.dataset.type === 'board';
            if (isBoard) {
                wrap.classList.add('opacity-50', 'pointer-events-none');
                sem.value = '';
            } else {
                wrap.classList.remove('opacity-50', 'pointer-events-none');
            }
        }

        function resetUploadLabels(docs) {
            form.querySelectorAll('[data-upload-input]').forEach(input => {
                const wrap = input.closest('div');
                if (!wrap) return;
                const label = wrap.querySelector('[data-upload-label]');
                if (label) label.textContent = 'Choose file';
                const link  = wrap.querySelector('[data-existing-link]');
                if (link) {
                    const docKey = input.name === 'photo'         ? 'photo_path'
                                 : input.name === 'student_sign'  ? 'student_sign_path'
                                 : input.name + '_path';
                    const url = docs && docs[docKey];
                    if (url) {
                        link.textContent = '✓ Already uploaded — pick a new file to replace it.';
                        link.classList.remove('hidden');
                    } else {
                        link.classList.add('hidden');
                    }
                }
            });
        }

        function beforeSubmit(_form) { /* hook for future client-side normalization */ }

        // Wire change handlers once (form lives in DOM permanently)
        form.querySelector('[data-student-uni]')?.addEventListener('change', () => { applyCourseFilter(); applyBoardSemester(); });
        form.querySelectorAll('[data-upload-input]').forEach(input => {
            input.addEventListener('change', () => {
                const wrap = input.closest('div');
                const label = wrap?.querySelector('[data-upload-label]');
                if (label) label.textContent = input.files[0]?.name || 'Choose file';
            });
        });

        return {
            openView: function (id) {
                const s = window.STUDENTS_DATA[id];
                if (!s) return;
                fillView(s);
                openSlide();
            },
            openCreate: function () {
                document.getElementById('studentFormTitle').textContent  = 'New Student Admission';
                document.getElementById('studentSubmitLabel').textContent = 'Save Student';
                document.getElementById('studentFormMethod').value = 'POST';
                form.action = window.STUDENT_STORE_URL;
                fillForm(null);
                closeSlide();
                openForm();
            },
            openEdit: function (id) {
                const s = window.STUDENTS_DATA[id];
                if (!s) return;
                document.getElementById('studentFormTitle').textContent  = 'Edit Student — ' + (s.name || '');
                document.getElementById('studentSubmitLabel').textContent = 'Save Changes';
                document.getElementById('studentFormMethod').value = 'PUT';
                form.action = window.STUDENT_UPDATE_URL_TEMPLATE.replace('__ID__', s.id);
                fillForm(s);
                closeSlide();
                openForm();
            },
            close:      close,
            closeForm:  closeForm,
            beforeSubmit: beforeSubmit,
        };
    })();

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') StudentsPanel.close();
    });

    // Open the create form when arriving via dashboard quick link (?panel=create).
    (function () {
        const params = new URLSearchParams(window.location.search);
        if (params.get('panel') === 'create') {
            StudentsPanel.openCreate();
        }
    })();
</script>
@endsection
