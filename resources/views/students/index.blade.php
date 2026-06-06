@extends('layouts.admin')

@section('title', 'Students - SSB Education')

@php
    $studentsData = $students->map(fn ($s) => [
        'id'              => $s->id,
        'name'            => $s->name,
        'mobile'          => $s->mobile,
        'email'           => $s->email,
        'admission_no'    => $s->admission_no,
        'class_name'      => $s->class_name,
        'university_id'   => $s->university_id,
        'university_name' => $s->university?->name,
        'course_id'       => $s->course_id,
        'course_name'     => $s->course?->name,
        'gender'          => $s->gender,
        'parent_name'     => $s->parent_name,
        'address'         => $s->address,
        'active'          => (bool) $s->active,
        'created_at'      => $s->created_at?->format('d M Y'),
    ])->keyBy('id');

    $statusChips = [
        'all'      => 'All',
        'active'   => 'Active',
        'inactive' => 'Inactive',
    ];

    $buildUrl = function (array $overrides) use ($status, $search) {
        $params = array_filter(array_merge([
            'status' => $status === 'all' ? null : $status,
            'q'      => $search !== '' ? $search : null,
        ], $overrides), fn ($v) => $v !== null && $v !== '');
        return route('students.index').($params ? '?'.http_build_query($params) : '');
    };
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

        <a href="{{ route('students.export') }}"
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

    {{-- Filter row --}}
    <div class="px-6 lg:px-10 py-2.5 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs">
        <div class="flex items-center gap-1.5 text-slate-500">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <span class="font-semibold text-slate-600">Filter by:</span>
        </div>

        <div class="flex items-center gap-1.5">
            <span class="text-slate-500">Status:</span>
            <div class="flex items-center gap-1">
                @foreach ($statusChips as $key => $label)
                    @php $isActive = $status === $key; @endphp
                    <a href="{{ $buildUrl(['status' => $key === 'all' ? null : $key]) }}"
                       class="px-3 py-1 rounded-full text-xs font-semibold transition
                              {{ $isActive
                                    ? 'bg-pink-600 text-white shadow-sm shadow-pink-500/30'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <form method="GET" action="{{ route('students.index') }}" class="ml-auto flex items-center gap-2">
            @if ($status !== 'all')
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>
                </div>
                <input type="text" name="q" value="{{ $search }}"
                       placeholder="Search name, mobile, admission, class..."
                       class="w-60 sm:w-72 pl-7 pr-3 py-1.5 bg-white border border-slate-200 rounded-full text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 transition">
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
</div>
@endsection

@section('admin')
{{-- LISTING --}}
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-[11px] font-semibold tracking-wider uppercase text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="text-left px-6 py-3 font-semibold">Student</th>
                    <th class="text-left px-6 py-3 font-semibold">Mobile</th>
                    <th class="text-left px-6 py-3 font-semibold">Admission No</th>
                    <th class="text-left px-6 py-3 font-semibold">Class</th>
                    <th class="text-left px-6 py-3 font-semibold">Status</th>
                    <th class="text-right px-6 py-3 font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($students as $s)
                    <tr class="student-row hover:bg-slate-50 transition cursor-pointer" data-student-id="{{ $s->id }}" onclick="StudentsPanel.openView({{ $s->id }})">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-pink-50 text-pink-600 font-bold text-sm flex items-center justify-center">
                                    {{ strtoupper(mb_substr($s->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium text-slate-800">{{ $s->name }}</div>
                                    @if ($s->parent_name)
                                        <div class="text-xs text-slate-500">{{ $s->parent_name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-slate-600">{{ $s->mobile }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $s->admission_no ?: '—' }}</td>
                        <td class="px-6 py-3 text-slate-600">{{ $s->class_name ?: '—' }}</td>
                        <td class="px-6 py-3">
                            @if ($s->active)
                                <span class="inline-flex items-center gap-1.5 text-emerald-700 text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-amber-700 text-xs font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-1" onclick="event.stopPropagation()">
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
                                            class="w-8 h-8 rounded-md text-slate-500 hover:bg-slate-100 hover:text-rose-600 inline-flex items-center justify-center transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
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
{{-- SLIDE-IN PANEL --}}
<aside id="slidePanel" class="absolute inset-0 z-30 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/30 opacity-0 transition-opacity duration-200" id="slidePanelBackdrop" onclick="StudentsPanel.close()"></div>
    <div id="slidePanelCard"
         class="absolute right-0 top-0 bottom-0 w-full max-w-xl bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-out">

        <button type="button" onclick="StudentsPanel.close()" aria-label="Close"
                class="absolute top-3 right-3 z-10 w-8 h-8 rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700 inline-flex items-center justify-center transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        {{-- VIEW MODE --}}
        <div id="panelView" class="panel-mode hidden flex-1 flex flex-col min-h-0">
            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                <div class="flex flex-col items-center text-center pb-5 border-b border-slate-100">
                    <div class="w-20 h-20 rounded-full bg-pink-50 text-pink-600 font-bold text-2xl flex items-center justify-center">
                        <span id="viewInitial"></span>
                    </div>
                    <h4 id="viewName" class="mt-3 text-base font-bold text-slate-800"></h4>
                    <p id="viewMobile" class="text-sm text-slate-500 mt-0.5"></p>
                    <span id="viewStatus" class="mt-2 inline-flex items-center gap-1.5 text-xs font-medium"></span>
                </div>

                <dl class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Admission No</dt>
                            <dd id="viewAdmission" class="mt-0.5 text-sm text-slate-800"></dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Class</dt>
                            <dd id="viewClass" class="mt-0.5 text-sm text-slate-800"></dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Gender</dt>
                            <dd id="viewGender" class="mt-0.5 text-sm text-slate-800 capitalize"></dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Parent</dt>
                            <dd id="viewParent" class="mt-0.5 text-sm text-slate-800"></dd>
                        </div>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Email</dt>
                        <dd id="viewEmail" class="mt-0.5 text-sm text-slate-800 break-words"></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Address</dt>
                        <dd id="viewAddress" class="mt-0.5 text-sm text-slate-800 whitespace-pre-line"></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Added</dt>
                        <dd id="viewCreated" class="mt-0.5 text-sm text-slate-800"></dd>
                    </div>
                </dl>
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

        {{-- CREATE FORM --}}
        <form id="createForm" method="POST" action="{{ route('students.store') }}" class="panel-mode hidden flex-1 flex flex-col min-h-0">
            @csrf
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                @include('students._fields')
            </div>
            <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                <button type="button" onclick="StudentsPanel.close()"
                        class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Add Student</button>
            </div>
        </form>

        {{-- EDIT FORM --}}
        <form id="editForm" method="POST" action="" class="panel-mode hidden flex-1 flex flex-col min-h-0">
            @csrf
            @method('PUT')
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                @include('students._fields')
            </div>
            <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                <button type="button" onclick="StudentsPanel.close()"
                        class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Save Changes</button>
            </div>
        </form>
    </div>
</aside>

<script>
    window.STUDENTS_DATA = @json($studentsData);
    window.STUDENT_UPDATE_URL_TEMPLATE = @json(url('/students/__ID__'));
    window.STUDENT_DESTROY_URL_TEMPLATE = @json(url('/students/__ID__'));

    const StudentsPanel = (function () {
        const panel    = document.getElementById('slidePanel');
        const card     = document.getElementById('slidePanelCard');
        const backdrop = document.getElementById('slidePanelBackdrop');
        const modes    = document.querySelectorAll('.panel-mode');

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

        function fillView(s) {
            document.getElementById('viewInitial').textContent  = (s.name || '?').charAt(0).toUpperCase();
            document.getElementById('viewName').textContent     = s.name;
            document.getElementById('viewMobile').textContent   = s.mobile;
            document.getElementById('viewAdmission').textContent= s.admission_no || '—';
            document.getElementById('viewClass').textContent    = s.class_name || '—';
            document.getElementById('viewGender').textContent   = s.gender || '—';
            document.getElementById('viewParent').textContent   = s.parent_name || '—';
            document.getElementById('viewEmail').textContent    = s.email || '—';
            document.getElementById('viewAddress').textContent  = s.address || '—';
            document.getElementById('viewCreated').textContent  = s.created_at || '—';

            const status = document.getElementById('viewStatus');
            if (s.active) {
                status.className = 'mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700';
                status.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active';
            } else {
                status.className = 'mt-2 inline-flex items-center gap-1.5 text-xs font-medium text-amber-700';
                status.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Inactive';
            }

            document.getElementById('viewDeleteForm').action = window.STUDENT_DESTROY_URL_TEMPLATE.replace('__ID__', s.id);
            document.getElementById('viewEditBtn').onclick = () => StudentsPanel.openEdit(s.id);
        }

        function fillForm(formId, s) {
            const f = document.getElementById(formId);
            f.querySelector('[name="name"]').value          = s?.name || '';
            f.querySelector('[name="mobile"]').value        = s?.mobile || '';
            f.querySelector('[name="email"]').value         = s?.email || '';
            f.querySelector('[name="admission_no"]').value  = s?.admission_no || '';
            f.querySelector('[name="class_name"]').value    = s?.class_name || '';
            f.querySelector('[name="university_id"]').value = s?.university_id || '';
            applyCourseFilter(f);
            f.querySelector('[name="course_id"]').value     = s?.course_id || '';
            f.querySelector('[name="gender"]').value        = s?.gender || '';
            f.querySelector('[name="parent_name"]').value   = s?.parent_name || '';
            f.querySelector('[name="address"]').value       = s?.address || '';
            f.querySelector('[name="active"]').checked      = s ? !!s.active : true;
        }

        // Hide/show course options based on the picked university.
        function applyCourseFilter(form) {
            const uni = form.querySelector('[data-student-uni]')?.value || '';
            const courseSel = form.querySelector('[data-student-course]');
            if (!courseSel) return;
            const selected = courseSel.value;
            courseSel.querySelectorAll('option').forEach(opt => {
                if (!opt.value) { opt.hidden = false; return; }
                const ok = !uni || opt.dataset.university === uni;
                opt.hidden = !ok;
                if (!ok && opt.value === selected) courseSel.value = '';
            });
        }
        document.querySelectorAll('[data-student-uni]').forEach(sel => {
            sel.addEventListener('change', () => applyCourseFilter(sel.closest('form')));
        });

        return {
            openView: function (id) {
                const s = window.STUDENTS_DATA[id];
                if (!s) return;
                fillView(s);
                show('panelView');
            },
            openCreate: function () {
                document.getElementById('createForm').reset();
                fillForm('createForm', null);
                show('createForm');
            },
            openEdit: function (id) {
                const s = window.STUDENTS_DATA[id];
                if (!s) return;
                const f = document.getElementById('editForm');
                f.action = window.STUDENT_UPDATE_URL_TEMPLATE.replace('__ID__', s.id);
                fillForm('editForm', s);
                show('editForm');
            },
            close: close,
        };
    })();

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') StudentsPanel.close();
    });

    // Open the create panel when arriving via dashboard quick link (?panel=create).
    (function () {
        const params = new URLSearchParams(window.location.search);
        if (params.get('panel') === 'create') {
            StudentsPanel.openCreate();
        }
    })();
</script>
@endsection
