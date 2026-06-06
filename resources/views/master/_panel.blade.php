@php
    /** @var bool $isAdmin */
    /** @var \Illuminate\Support\Collection $allUniversities */
    /** @var \Illuminate\Support\Collection $allCoursesData */
@endphp

<aside id="masterPanel" class="fixed inset-0 z-30 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/30 opacity-0 transition-opacity duration-200" id="masterPanelBackdrop" onclick="MasterPanel.close()"></div>
    <div id="masterPanelCard"
         style="top: var(--topbar-h, 64px)"
         class="absolute right-0 bottom-0 w-full max-w-xl bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-out">

        <button type="button" onclick="MasterPanel.close()" aria-label="Close"
                class="absolute top-3 right-3 z-10 w-8 h-8 rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700 inline-flex items-center justify-center transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        {{-- ────────────── UNIVERSITY: VIEW ────────────── --}}
        <div id="viewUniversity" class="master-mode hidden flex-1 flex flex-col min-h-0">
            <div class="flex-1 overflow-y-auto px-6 pt-12 pb-6 space-y-5">
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <img id="viewUniversityImage" src="" alt="" class="w-14 h-14 rounded-md object-cover bg-slate-100 hidden">
                    <div id="viewUniversityInitial" class="w-14 h-14 rounded-md bg-pink-50 text-pink-600 font-bold text-xl flex items-center justify-center"></div>
                    <div class="min-w-0">
                        <h4 id="viewUniversityName" class="text-base font-bold text-slate-800 truncate"></h4>
                        <span id="viewUniversityType" class="inline-block text-[10px] font-semibold px-1.5 py-0.5 rounded bg-pink-50 text-pink-700 mt-1"></span>
                    </div>
                </div>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Address</dt>
                        <dd id="viewUniversityAddress" class="mt-0.5 text-sm text-slate-800 whitespace-pre-line"></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Website</dt>
                        <dd id="viewUniversityWebsite" class="mt-0.5 text-sm text-slate-800 break-words"></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Registration Fee</dt>
                        <dd id="viewUniversityFee" class="mt-0.5 text-sm text-slate-800 font-semibold"></dd>
                    </div>
                </dl>
            </div>
            @if ($isAdmin)
                <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                    <form id="viewUniversityDeleteForm" method="POST" action=""
                          onsubmit="return confirmAction(this, 'Delete this university? All linked courses and fee structures will also be removed.', 'Delete university');">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-rose-600 hover:text-rose-700 transition">Delete</button>
                    </form>
                    <button type="button" id="viewUniversityEdit" class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Edit University</button>
                </div>
            @endif
        </div>

        {{-- ────────────── COURSE: VIEW ────────────── --}}
        <div id="viewCourse" class="master-mode hidden flex-1 flex flex-col min-h-0">
            <div class="flex-1 overflow-y-auto px-6 pt-12 pb-6 space-y-5">
                <div class="pb-4 border-b border-slate-100">
                    <p id="viewCourseUniversity" class="text-[11px] font-semibold uppercase tracking-wider text-pink-600"></p>
                    <h4 id="viewCourseName" class="mt-1 text-base font-bold text-slate-800"></h4>
                </div>
                <dl class="grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Mode</dt>
                        <dd id="viewCourseMode" class="mt-0.5 text-sm text-slate-800 capitalize"></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Duration</dt>
                        <dd id="viewCourseDuration" class="mt-0.5 text-sm text-slate-800"></dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Lateral Entry</dt>
                        <dd id="viewCourseLateral" class="mt-0.5 text-sm text-slate-800"></dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Subjects</dt>
                        <dd id="viewCourseSubjects" class="mt-0.5 text-sm text-slate-800 whitespace-pre-line"></dd>
                    </div>
                </dl>
            </div>
            @if ($isAdmin)
                <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                    <form id="viewCourseDeleteForm" method="POST" action=""
                          onsubmit="return confirmAction(this, 'Delete this course? The linked fee structure will also be removed.', 'Delete course');">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-rose-600 hover:text-rose-700 transition">Delete</button>
                    </form>
                    <button type="button" id="viewCourseEdit" class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Edit Course</button>
                </div>
            @endif
        </div>

        {{-- ────────────── FEE: VIEW ────────────── --}}
        <div id="viewFee" class="master-mode hidden flex-1 flex flex-col min-h-0">
            <div class="flex-1 overflow-y-auto px-6 pt-12 pb-6 space-y-5">
                <div class="pb-4 border-b border-slate-100">
                    <p id="viewFeeUniversity" class="text-[11px] font-semibold uppercase tracking-wider text-pink-600"></p>
                    <h4 id="viewFeeCourse" class="mt-1 text-base font-bold text-slate-800"></h4>
                    <p id="viewFeeDuration" class="text-xs text-slate-500 mt-0.5"></p>
                </div>
                <dl class="space-y-3">
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-slate-500">Fee per semester</dt>
                        <dd id="viewFeePerSem" class="text-sm font-semibold text-slate-800"></dd>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                        <dt class="text-sm font-semibold text-slate-700">Total Fee</dt>
                        <dd id="viewFeeTotal" class="text-base font-bold text-pink-600"></dd>
                    </div>
                </dl>
            </div>
            @if ($isAdmin)
                <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                    <form id="viewFeeDeleteForm" method="POST" action=""
                          onsubmit="return confirmAction(this, 'Delete this fee structure?', 'Delete fee structure');">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-4 py-2 text-sm font-semibold text-rose-600 hover:text-rose-700 transition">Delete</button>
                    </form>
                    <button type="button" id="viewFeeEdit" class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Edit Fee Structure</button>
                </div>
            @endif
        </div>

        @if ($isAdmin)
            {{-- ────────────── UNIVERSITY: CREATE ────────────── --}}
            <form id="formUniversityCreate" method="POST" action="{{ route('master.universities.store') }}" enctype="multipart/form-data" class="master-mode hidden flex-1 flex flex-col min-h-0">
                @csrf
                <div class="flex-1 overflow-y-auto px-6 pt-12 pb-6 space-y-4">
                    @include('master._fields_university')
                </div>
                <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                    <button type="button" onclick="MasterPanel.close()" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Add University</button>
                </div>
            </form>

            {{-- ────────────── UNIVERSITY: EDIT ────────────── --}}
            <form id="formUniversityEdit" method="POST" action="" enctype="multipart/form-data" class="master-mode hidden flex-1 flex flex-col min-h-0">
                @csrf @method('PUT')
                <div class="flex-1 overflow-y-auto px-6 pt-12 pb-6 space-y-4">
                    @include('master._fields_university')
                </div>
                <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                    <button type="button" onclick="MasterPanel.close()" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Save Changes</button>
                </div>
            </form>

            {{-- ────────────── COURSE: CREATE ────────────── --}}
            <form id="formCourseCreate" method="POST" action="{{ route('master.courses.store') }}" class="master-mode hidden flex-1 flex flex-col min-h-0">
                @csrf
                <div class="flex-1 overflow-y-auto px-6 pt-12 pb-6 space-y-4">
                    @include('master._fields_course', ['allUniversities' => $allUniversities])
                </div>
                <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                    <button type="button" onclick="MasterPanel.close()" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Add Course</button>
                </div>
            </form>

            {{-- ────────────── COURSE: EDIT ────────────── --}}
            <form id="formCourseEdit" method="POST" action="" class="master-mode hidden flex-1 flex flex-col min-h-0">
                @csrf @method('PUT')
                <div class="flex-1 overflow-y-auto px-6 pt-12 pb-6 space-y-4">
                    @include('master._fields_course', ['allUniversities' => $allUniversities])
                </div>
                <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                    <button type="button" onclick="MasterPanel.close()" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Save Changes</button>
                </div>
            </form>

            {{-- ────────────── FEE: CREATE ────────────── --}}
            <form id="formFeeCreate" method="POST" action="{{ route('master.fees.store') }}" class="master-mode fee-form hidden flex-1 flex flex-col min-h-0">
                @csrf
                <div class="flex-1 overflow-y-auto px-6 pt-12 pb-6 space-y-4">
                    @include('master._fields_fee', ['allUniversities' => $allUniversities])
                </div>
                <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                    <button type="button" onclick="MasterPanel.close()" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Add Fee Structure</button>
                </div>
            </form>

            {{-- ────────────── FEE: EDIT ────────────── --}}
            <form id="formFeeEdit" method="POST" action="" class="master-mode fee-form hidden flex-1 flex flex-col min-h-0">
                @csrf @method('PUT')
                <div class="flex-1 overflow-y-auto px-6 pt-12 pb-6 space-y-4">
                    @include('master._fields_fee', ['allUniversities' => $allUniversities])
                </div>
                <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                    <button type="button" onclick="MasterPanel.close()" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Save Changes</button>
                </div>
            </form>
        @endif
    </div>
</aside>
