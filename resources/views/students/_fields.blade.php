{{-- Shared fields for the full-screen create + edit student admission form. --}}
@php
    /** @var string $mode 'create' or 'edit' (defaults to create) */
    $mode = $mode ?? 'create';
    $unisOnly   = ($allUniversities ?? collect())->where('type', \App\Models\University::TYPE_UNIVERSITY);
    $boardsOnly = ($allUniversities ?? collect())->where('type', \App\Models\University::TYPE_BOARD);
    $categories = [
        'general' => 'General',
        'obc'     => 'OBC',
        'sc'      => 'SC',
        'st'      => 'ST',
        'minor'   => 'Minor',
        'nri'     => 'NRI',
        'other'   => 'Other',
    ];
    $academicLevels = ['X', 'XII', 'UG', 'PG', 'OTHER'];
@endphp

{{-- ========= Section: Academic placement ========= --}}
<section class="bg-white rounded-xl border border-slate-200 p-5 space-y-4">
    <div class="flex items-center gap-2">
        <div class="w-1 h-5 rounded-full bg-gradient-to-b from-fuchsia-500 to-pink-500"></div>
        <h3 class="text-sm font-bold text-slate-800">Course Selection</h3>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">University / Board <span class="text-rose-500">*</span></label>
            <select name="university_id" data-student-uni required
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
                <option value="">Select…</option>
                @if ($unisOnly->isNotEmpty())
                    <optgroup label="Universities">
                        @foreach ($unisOnly as $u)<option value="{{ $u->id }}" data-type="university">{{ $u->name }}</option>@endforeach
                    </optgroup>
                @endif
                @if ($boardsOnly->isNotEmpty())
                    <optgroup label="Boards">
                        @foreach ($boardsOnly as $u)<option value="{{ $u->id }}" data-type="board">{{ $u->name }}</option>@endforeach
                    </optgroup>
                @endif
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Type</label>
            <select name="enrollment_type" data-student-enrolltype
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
                <option value="">—</option>
                {{-- University options --}}
                <option value="online" data-for="university">Online</option>
                <option value="odl"    data-for="university">ODL</option>
                {{-- Board options --}}
                <option value="fresh_board" data-for="board">Fresh</option>
                <option value="toc"         data-for="board">TOC</option>
                <option value="part"        data-for="board">Part Admission</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Course</label>
            <select name="course_id" data-student-course
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
                <option value="">—</option>
                @foreach (($allCourses ?? collect()) as $c)
                    <option value="{{ $c->id }}" data-university="{{ $c->university_id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-[11px] text-slate-400">Course list filters to the selected university.</p>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Year <span class="text-[10px] font-normal text-slate-400">(duration)</span></label>
            <select name="course_year"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
                <option value="">—</option>
                @for ($i = 1; $i <= 6; $i++)<option value="{{ $i }}">Year {{ $i }}</option>@endfor
            </select>
        </div>

        <div data-semester-wrap>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Semester</label>
            <select name="semester"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
                <option value="">—</option>
                @for ($i = 1; $i <= 12; $i++)<option value="{{ $i }}">Semester {{ $i }}</option>@endfor
            </select>
            <p class="mt-1 text-[11px] text-slate-400" data-semester-hint>Boards collect Year only.</p>
        </div>
    </div>
</section>

{{-- ========= Section: Personal details ========= --}}
<section class="bg-white rounded-xl border border-slate-200 p-5 space-y-4">
    <div class="flex items-center gap-2">
        <div class="w-1 h-5 rounded-full bg-gradient-to-b from-fuchsia-500 to-pink-500"></div>
        <h3 class="text-sm font-bold text-slate-800">Student Details</h3>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Student Name <span class="text-rose-500">*</span></label>
            <input type="text" name="name" required maxlength="255"
                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Father's Name</label>
            <input type="text" name="father_name" maxlength="255"
                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Mother's Name</label>
            <input type="text" name="mother_name" maxlength="255"
                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Gender</label>
            <select name="gender"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
                <option value="">—</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Date of Birth</label>
            <input type="date" name="dob"
                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Category</label>
            <select name="category"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
                <option value="">—</option>
                @foreach ($categories as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nationality</label>
            <input type="text" name="nationality" maxlength="64" placeholder="e.g. Indian"
                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Religion</label>
            <input type="text" name="religion" maxlength="64"
                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Aadhar Number</label>
            <input type="text" name="aadhar_number" maxlength="20" inputmode="numeric"
                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile <span class="text-rose-500">*</span></label>
            <input type="tel" name="mobile" required inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15"
                   placeholder="10-digit mobile"
                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
            <input type="email" name="email" maxlength="255"
                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Admission No <span class="text-[10px] font-normal text-slate-400">(auto)</span></label>
            <input type="text" name="admission_no" maxlength="50" placeholder="Leave blank — auto-generated from 1001"
                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
        </div>
    </div>
</section>

{{-- ========= Section: Address ========= --}}
<section class="bg-white rounded-xl border border-slate-200 p-5 space-y-4">
    <div class="flex items-center gap-2">
        <div class="w-1 h-5 rounded-full bg-gradient-to-b from-fuchsia-500 to-pink-500"></div>
        <h3 class="text-sm font-bold text-slate-800">Address</h3>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <div class="sm:col-span-2 lg:col-span-3">
            <label class="block text-xs font-semibold text-slate-700 mb-1">Address</label>
            <textarea name="address" rows="2" maxlength="1000"
                      class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800"></textarea>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Country</label>
            <input type="text" name="country" maxlength="64"
                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">State</label>
            <input type="text" name="state" maxlength="64"
                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">City</label>
            <input type="text" name="city" maxlength="64"
                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Pincode</label>
            <input type="text" name="pincode" maxlength="12" inputmode="numeric"
                   class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800">
        </div>
    </div>
</section>

{{-- ========= Section: Uploads ========= --}}
<section class="bg-white rounded-xl border border-slate-200 p-5 space-y-4">
    <div class="flex items-center gap-2">
        <div class="w-1 h-5 rounded-full bg-gradient-to-b from-emerald-500 to-teal-500"></div>
        <h3 class="text-sm font-bold text-slate-800">Uploads</h3>
        <span class="text-[11px] text-slate-400">PNG / JPG / PDF — up to 4MB each</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @php
            $uploads = [
                ['name' => 'photo',                'label' => 'Student Photo'],
                ['name' => 'student_sign',         'label' => 'Student Sign'],
                ['name' => 'aadhar_front',         'label' => 'Aadhar Front'],
                ['name' => 'aadhar_back',          'label' => 'Aadhar Back'],
                ['name' => 'marksheet_x',          'label' => 'X Marksheet'],
                ['name' => 'marksheet_xii',        'label' => 'XII Marksheet'],
                ['name' => 'marksheet_graduation', 'label' => 'Graduation Marksheet'],
                ['name' => 'abc_id',               'label' => 'ABC ID'],
                ['name' => 'deb_id',               'label' => 'DEB ID'],
                ['name' => 'other_doc',            'label' => 'Other Document'],
            ];
        @endphp

        @foreach ($uploads as $u)
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">{{ $u['label'] }}</label>
                <label class="flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-50 border border-dashed border-slate-300 text-xs text-slate-500 hover:bg-slate-100 cursor-pointer transition">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 14l5-5 5 5M12 9v12"/></svg>
                    <span data-upload-label class="truncate">Choose file</span>
                    <input type="file" name="{{ $u['name'] }}" accept="image/*,application/pdf"
                           class="hidden" data-upload-input>
                </label>
                <p class="hidden mt-1 text-[11px] text-emerald-600" data-existing-link></p>
            </div>
        @endforeach
    </div>
</section>

{{-- ========= Section: Academic records (X / XII / UG / PG / OTHER) ========= --}}
<section class="bg-white rounded-xl border border-slate-200 p-5 space-y-4">
    <div class="flex items-center gap-2">
        <div class="w-1 h-5 rounded-full bg-gradient-to-b from-amber-500 to-orange-500"></div>
        <h3 class="text-sm font-bold text-slate-800">Academic Records</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-[11px] font-semibold tracking-wider uppercase text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="text-left py-2 pr-3 font-semibold">Examination</th>
                    <th class="text-left py-2 px-3 font-semibold">Board / University</th>
                    <th class="text-left py-2 px-3 font-semibold">Subject</th>
                    <th class="text-left py-2 px-3 font-semibold">Year of Passing</th>
                    <th class="text-left py-2 pl-3 font-semibold">Division / Grade</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($academicLevels as $idx => $level)
                    <tr>
                        <td class="py-2 pr-3 align-middle text-slate-700 font-semibold">
                            {{ $level }}
                            <input type="hidden" name="academic_level[]" value="{{ $level }}">
                        </td>
                        <td class="py-2 px-3">
                            <input type="text" name="academic_board[]" maxlength="255"
                                   class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-md focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-xs text-slate-800">
                        </td>
                        <td class="py-2 px-3">
                            <input type="text" name="academic_subject[]" maxlength="255"
                                   class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-md focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-xs text-slate-800">
                        </td>
                        <td class="py-2 px-3">
                            <input type="text" name="academic_year[]" maxlength="10" inputmode="numeric"
                                   class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-md focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-xs text-slate-800">
                        </td>
                        <td class="py-2 pl-3">
                            <input type="text" name="academic_grade[]" maxlength="20"
                                   class="w-full px-2.5 py-1.5 bg-white border border-slate-200 rounded-md focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-xs text-slate-800">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

{{-- ========= Section: Status ========= --}}
<section class="bg-white rounded-xl border border-slate-200 p-5">
    <label class="inline-flex items-center gap-3 cursor-pointer select-none">
        <span class="relative">
            <input type="hidden" name="active" value="0">
            <input name="active" type="checkbox" value="1" checked
                   class="peer sr-only">
            <span class="block w-11 h-6 rounded-full bg-slate-200 peer-checked:bg-emerald-500 transition"></span>
            <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
        </span>
        <span class="text-sm font-semibold text-slate-700">Active</span>
        <span class="text-xs text-slate-500">— inactive students stay in the system but are skipped in pickers.</span>
    </label>
</section>
