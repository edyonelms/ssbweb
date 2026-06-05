@php
    /** @var \Illuminate\Support\Collection $allUniversities */
@endphp

<div>
    <label class="block text-xs font-semibold text-slate-700 mb-1">University / Board <span class="text-rose-500">*</span></label>
    <select name="university_id_picker"
            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
        <option value="">Select university</option>
        @foreach ($allUniversities as $u)
            <option value="{{ $u->id }}">{{ $u->name }} ({{ ucfirst($u->type) }})</option>
        @endforeach
    </select>
    <p class="mt-1 text-[11px] text-slate-400">Filters the course list below — not saved on its own.</p>
</div>

<div>
    <label class="block text-xs font-semibold text-slate-700 mb-1">Course <span class="text-rose-500">*</span></label>
    <select name="course_id" required
            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
        <option value="">Select course</option>
    </select>
</div>

<div>
    <label class="block text-xs font-semibold text-slate-700 mb-1">Fee per semester (₹) <span class="text-rose-500">*</span></label>
    <input type="number" step="1" min="0" name="fee_per_sem" required
           placeholder="e.g. 25000"
           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
</div>

<div class="rounded-xl bg-pink-50/60 border border-pink-100 p-3 space-y-1.5">
    <div class="flex items-center justify-between text-xs text-slate-600">
        <span>Semesters (from course duration)</span>
        <span data-semesters class="font-semibold text-slate-800">0</span>
    </div>
    <div class="flex items-center justify-between border-t border-pink-100 pt-1.5 text-sm">
        <span class="font-semibold text-slate-700">Total Fee</span>
        <span data-total-fee class="text-base font-bold text-pink-600">₹0</span>
    </div>
</div>
