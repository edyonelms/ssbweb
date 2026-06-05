@php
    /** @var \Illuminate\Support\Collection $allUniversities */
@endphp

<div>
    <label class="block text-xs font-semibold text-slate-700 mb-1">University / Board <span class="text-rose-500">*</span></label>
    <select name="university_id" required
            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
        <option value="">Select university</option>
        @foreach ($allUniversities as $u)
            <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->type) }})</option>
        @endforeach
    </select>
</div>

<div>
    <label class="block text-xs font-semibold text-slate-700 mb-1">Course Name <span class="text-rose-500">*</span></label>
    <input type="text" name="name" required maxlength="255"
           placeholder="e.g. B.Tech CSE"
           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Mode</label>
        <select name="mode"
                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
            <option value="">—</option>
            <option value="regular">Regular</option>
            <option value="distance">Distance</option>
            <option value="online">Online</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Duration (years) <span class="text-rose-500">*</span></label>
        <input type="number" step="0.5" min="0.5" max="10" name="duration_years" required
               placeholder="e.g. 4"
               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
    </div>
</div>

<label class="inline-flex items-center gap-2 select-none">
    <input type="checkbox" name="lateral_entry" value="1"
           class="w-4 h-4 rounded border-slate-300 text-pink-600 focus:ring-pink-300/60">
    <span class="text-sm text-slate-700">Allows lateral entry</span>
</label>

<div>
    <label class="block text-xs font-semibold text-slate-700 mb-1">Subjects</label>
    <textarea name="subjects" rows="3" maxlength="2000"
              placeholder="Comma-separated list of subjects (e.g. Mathematics, Physics, Programming...)"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm"></textarea>
</div>
