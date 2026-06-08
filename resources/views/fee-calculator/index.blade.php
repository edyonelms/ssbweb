@extends('layouts.admin')

@section('title', 'Fee Calculator - SSB Education')

@php
    /** @var \Illuminate\Support\Collection $universities */
    /** @var \Illuminate\Support\Collection $coursesData */
    /** @var array $stats */
@endphp

@section('admin-header')
<div class="sticky top-0 z-20 bg-white border-b border-slate-200">
    <div class="px-6 lg:px-10 py-3 flex flex-wrap items-center gap-x-6 gap-y-2">
        <div class="mr-auto">
            <h2 class="text-base font-bold text-slate-800">Fee Calculator</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pick a university and a course, drop in a discount, see the breakdown</p>
        </div>
        <div class="flex items-center gap-x-6 gap-y-1 text-xs text-slate-500 flex-wrap">
            <span>Universities: <span class="text-slate-800 font-semibold ml-1">{{ $stats['universities'] }}</span></span>
            <span>Courses: <span class="text-pink-600 font-semibold ml-1">{{ $stats['courses'] }}</span></span>
            <span>Fee Structures: <span class="text-emerald-600 font-semibold ml-1">{{ $stats['fees'] }}</span></span>
        </div>
    </div>
</div>
@endsection

@section('admin')
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- ── INPUTS (left, 2/5) ── --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6 space-y-5">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Inputs</h3>
            <p class="text-[11px] text-slate-500 mt-0.5">Change anything below — the breakdown updates live.</p>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">University / Board <span class="text-rose-500">*</span></label>
            <select id="universitySelect"
                    class="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                <option value="">Select university</option>
                @foreach ($universities as $u)
                    <option value="{{ $u->id }}" data-registration="{{ (float) $u->registration_fee }}">
                        {{ $u->name }} ({{ ucfirst($u->type) }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Course <span class="text-rose-500">*</span></label>
            <div class="relative" id="courseCombo">
                <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
                    </svg>
                </span>
                <input type="text" id="courseSearch" disabled autocomplete="off"
                       placeholder="Pick a university first"
                       class="w-full pl-9 pr-9 py-2.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm disabled:bg-slate-50 disabled:text-slate-400 disabled:placeholder-slate-400">
                <button type="button" id="courseClear" tabindex="-1"
                        class="hidden absolute inset-y-0 right-2 flex items-center justify-center w-7 text-slate-400 hover:text-rose-500"
                        aria-label="Clear course">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <input type="hidden" id="courseSelect" value="">
                <div id="courseDropdown"
                     class="hidden absolute z-30 left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white border border-slate-200 rounded-lg shadow-lg ring-1 ring-slate-100/50"></div>
            </div>
            <p id="courseHint" class="mt-1 text-[11px] text-slate-400">—</p>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Discount <span class="text-rose-500">*</span></label>
            <div class="relative">
                <input type="number" id="discountInput" min="0" max="100" step="0.5" value="0"
                       class="w-full px-3 py-2.5 pr-12 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                <span class="absolute inset-y-0 right-3 flex items-center text-sm font-semibold text-slate-400">%</span>
            </div>

            {{-- Quick discount chips --}}
            <div class="mt-2 flex flex-wrap gap-1.5">
                @foreach ([0, 10, 20, 30, 40, 50] as $pct)
                    <button type="button" data-discount-chip="{{ $pct }}"
                            class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 hover:bg-pink-100 hover:text-pink-700 transition">
                        {{ $pct }}%
                    </button>
                @endforeach
            </div>
        </div>

        <div class="pt-3 border-t border-slate-100">
            <button type="button" id="resetBtn"
                    class="w-full px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-semibold transition">
                Reset
            </button>
        </div>
    </div>

    {{-- ── BREAKDOWN (right, 3/5) ── --}}
    <div class="lg:col-span-3 space-y-6">

        {{-- Headline summary cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="rounded-xl bg-gradient-to-br from-pink-50 to-rose-50 border border-pink-100 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-pink-700">Per Semester</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-800" data-result="per_sem">₹0</p>
                <p class="mt-0.5 text-[11px] text-slate-500"><s data-result="per_sem_original" class="text-slate-400">₹0</s></p>
            </div>
            <div class="rounded-xl bg-gradient-to-br from-violet-50 to-fuchsia-50 border border-violet-100 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-violet-700">Per Year</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-800" data-result="per_year">₹0</p>
                <p class="mt-0.5 text-[11px] text-slate-500"><s data-result="per_year_original" class="text-slate-400">₹0</s></p>
            </div>
            <div class="rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 p-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-700">Overall</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-800" data-result="total">₹0</p>
                <p class="mt-0.5 text-[11px] text-slate-500"><s data-result="total_original" class="text-slate-400">₹0</s></p>
            </div>
        </div>

        {{-- Detailed breakdown --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Breakdown</h3>
                    <p class="text-[11px] text-slate-500">Discount applied to the base course fee. Registration fee shown separately.</p>
                </div>
                <span id="discountBadge" class="text-[10px] font-semibold px-2 py-1 rounded-full bg-slate-100 text-slate-600">0% off</span>
            </div>

            <dl class="space-y-3 text-sm">
                <div class="flex items-center justify-between">
                    <dt class="text-slate-500">Course duration</dt>
                    <dd class="font-semibold text-slate-700" data-result="duration">—</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-slate-500">Semesters</dt>
                    <dd class="font-semibold text-slate-700" data-result="semesters">0</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-slate-500">Base fee per semester</dt>
                    <dd class="font-semibold text-slate-700" data-result="base_per_sem">₹0</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-slate-500">Base total fee</dt>
                    <dd class="font-semibold text-slate-700" data-result="base_total">₹0</dd>
                </div>
                <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                    <dt class="text-rose-600">Discount applied</dt>
                    <dd class="font-semibold text-rose-600" data-result="discount_amount">− ₹0</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-slate-500">Registration fee <span class="text-[10px] text-slate-400 ml-1">(one-time)</span></dt>
                    <dd class="font-semibold text-slate-700" data-result="registration">₹0</dd>
                </div>
                <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                    <dt class="text-base font-bold text-slate-800">Total payable</dt>
                    <dd class="text-base font-extrabold text-pink-600" data-result="grand_total">₹0</dd>
                </div>
            </dl>

            <div id="emptyHint" class="mt-6 rounded-lg bg-slate-50 border border-dashed border-slate-200 p-4 text-center text-xs text-slate-500">
                Pick a university and a course on the left to see the breakdown.
            </div>
            <div id="noFeeHint" class="hidden mt-6 rounded-lg bg-amber-50 border border-amber-100 p-4 text-center text-xs text-amber-700">
                No fee structure set up for this course yet. Ask the admin to add one under Master Data → Fee Structure.
            </div>
        </div>
    </div>
</div>

<script>
    window.COURSES_DATA = @json($coursesData);

    (function () {
        const uniSelect    = document.getElementById('universitySelect');
        const courseSelect = document.getElementById('courseSelect');
        const courseSearch = document.getElementById('courseSearch');
        const courseClear  = document.getElementById('courseClear');
        const courseDropdown = document.getElementById('courseDropdown');
        const courseCombo  = document.getElementById('courseCombo');
        const courseHint   = document.getElementById('courseHint');
        const discount     = document.getElementById('discountInput');
        const discountBadge= document.getElementById('discountBadge');
        const resetBtn     = document.getElementById('resetBtn');
        const emptyHint    = document.getElementById('emptyHint');
        const noFeeHint    = document.getElementById('noFeeHint');

        let courseOptions = [];

        const fmt = (n) => '₹' + Math.round(Number(n || 0)).toLocaleString('en-IN');

        function setText(key, value) {
            document.querySelectorAll('[data-result="' + key + '"]').forEach(el => {
                el.textContent = value;
            });
        }

        function clearResults() {
            ['per_sem','per_year','total','per_sem_original','per_year_original','total_original',
             'base_per_sem','base_total','discount_amount','registration','grand_total']
                .forEach(k => setText(k, k === 'discount_amount' ? '− ₹0' : '₹0'));
            setText('duration', '—');
            setText('semesters', '0');
            discountBadge.textContent = (Number(discount.value) || 0) + '% off';
            discountBadge.className = 'text-[10px] font-semibold px-2 py-1 rounded-full bg-slate-100 text-slate-600';
        }

        function closeCourseDropdown() {
            courseDropdown.classList.add('hidden');
        }

        function openCourseDropdown() {
            if (courseOptions.length === 0) return;
            renderCourseDropdown(courseSearch.value || '');
            courseDropdown.classList.remove('hidden');
        }

        function renderCourseDropdown(query) {
            const q = (query || '').trim().toLowerCase();
            const matches = q
                ? courseOptions.filter(c => c.name.toLowerCase().includes(q))
                : courseOptions.slice();

            courseDropdown.innerHTML = '';
            if (matches.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'px-3 py-2.5 text-xs text-slate-400';
                empty.textContent = 'No courses match "' + query + '"';
                courseDropdown.appendChild(empty);
                return;
            }

            matches.forEach(c => {
                const opt = document.createElement('button');
                opt.type = 'button';
                opt.dataset.courseId = c.id;
                opt.className = 'w-full text-left px-3 py-2 text-sm text-slate-700 hover:bg-pink-50 hover:text-pink-700 transition flex items-center justify-between gap-3';
                if (String(c.id) === String(courseSelect.value)) {
                    opt.classList.add('bg-pink-50', 'text-pink-700', 'font-semibold');
                }
                const left = document.createElement('span');
                left.className = 'truncate';
                left.textContent = c.name;
                const right = document.createElement('span');
                right.className = 'text-[11px] text-slate-400 whitespace-nowrap';
                right.textContent = c.duration_years + ' yrs';
                opt.appendChild(left);
                opt.appendChild(right);
                opt.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    selectCourse(c);
                });
                courseDropdown.appendChild(opt);
            });
        }

        function selectCourse(c) {
            courseSelect.value = c.id;
            courseSearch.value = c.name + ' · ' + c.duration_years + ' yrs';
            courseClear.classList.remove('hidden');
            closeCourseDropdown();
            recalc();
        }

        function clearCourseSelection() {
            courseSelect.value = '';
            courseSearch.value = '';
            courseClear.classList.add('hidden');
            recalc();
        }

        function rebuildCourseDropdown() {
            const uniId = parseInt(uniSelect.value || 0, 10);
            courseSelect.value = '';
            courseSearch.value = '';
            courseClear.classList.add('hidden');
            closeCourseDropdown();

            if (!uniId) {
                courseOptions = [];
                courseSearch.disabled = true;
                courseSearch.placeholder = 'Pick a university first';
                courseHint.textContent = '—';
                return;
            }

            courseOptions = window.COURSES_DATA.filter(c => c.university_id === uniId);
            if (courseOptions.length === 0) {
                courseSearch.disabled = true;
                courseSearch.placeholder = 'No courses for this university yet';
                courseHint.textContent = 'Add a course under Master Data → Courses.';
                return;
            }

            courseSearch.disabled = false;
            courseSearch.placeholder = 'Search course…';
            courseHint.textContent = courseOptions.length + ' course' + (courseOptions.length === 1 ? '' : 's') + ' available.';
        }

        function recalc() {
            const courseId = parseInt(courseSelect.value || 0, 10);
            const course   = window.COURSES_DATA.find(c => c.id === courseId);
            const uniOpt   = uniSelect.selectedOptions[0];
            const regFee   = parseFloat(uniOpt?.dataset?.registration || 0);
            const pct      = Math.min(100, Math.max(0, parseFloat(discount.value || 0)));

            discountBadge.textContent = pct + '% off';
            discountBadge.className = pct > 0
                ? 'text-[10px] font-semibold px-2 py-1 rounded-full bg-emerald-100 text-emerald-700'
                : 'text-[10px] font-semibold px-2 py-1 rounded-full bg-slate-100 text-slate-600';

            if (!course) {
                clearResults();
                emptyHint.classList.remove('hidden');
                noFeeHint.classList.add('hidden');
                return;
            }
            emptyHint.classList.add('hidden');

            if (!course.has_fee || course.fee_per_sem <= 0) {
                clearResults();
                setText('duration', course.duration_years + ' years');
                setText('semesters', course.semesters);
                setText('registration', fmt(regFee));
                setText('grand_total', fmt(regFee));
                noFeeHint.classList.remove('hidden');
                return;
            }
            noFeeHint.classList.add('hidden');

            const sems       = course.semesters;
            const basePerSem = course.fee_per_sem;
            const baseTotal  = basePerSem * sems;
            const baseYear   = basePerSem * 2;
            const multiplier = 1 - (pct / 100);
            const discPerSem = basePerSem * multiplier;
            const discYear   = baseYear * multiplier;
            const discTotal  = baseTotal * multiplier;
            const discAmount = baseTotal - discTotal;
            const grandTotal = discTotal + regFee;

            setText('per_sem',          fmt(discPerSem));
            setText('per_year',         fmt(discYear));
            setText('total',            fmt(discTotal));
            setText('per_sem_original', fmt(basePerSem));
            setText('per_year_original',fmt(baseYear));
            setText('total_original',   fmt(baseTotal));

            setText('duration',         course.duration_years + ' years');
            setText('semesters',        sems);
            setText('base_per_sem',     fmt(basePerSem));
            setText('base_total',       fmt(baseTotal));
            setText('discount_amount',  '− ' + fmt(discAmount));
            setText('registration',     fmt(regFee));
            setText('grand_total',      fmt(grandTotal));
        }

        uniSelect.addEventListener('change', () => { rebuildCourseDropdown(); recalc(); });
        discount.addEventListener('input', recalc);

        courseSearch.addEventListener('focus', openCourseDropdown);
        courseSearch.addEventListener('input', () => {
            if (courseSelect.value) {
                courseSelect.value = '';
                courseClear.classList.add('hidden');
                recalc();
            }
            renderCourseDropdown(courseSearch.value);
            courseDropdown.classList.toggle('hidden', courseOptions.length === 0);
        });
        courseSearch.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeCourseDropdown();
                courseSearch.blur();
            }
        });
        courseClear.addEventListener('mousedown', (e) => {
            e.preventDefault();
            clearCourseSelection();
            courseSearch.focus();
        });
        document.addEventListener('mousedown', (e) => {
            if (!courseCombo.contains(e.target)) closeCourseDropdown();
        });

        document.querySelectorAll('[data-discount-chip]').forEach(btn => {
            btn.addEventListener('click', () => {
                discount.value = btn.dataset.discountChip;
                recalc();
            });
        });

        resetBtn.addEventListener('click', () => {
            uniSelect.value = '';
            rebuildCourseDropdown();
            discount.value = 0;
            recalc();
        });

        recalc();
    })();
</script>
@endsection
