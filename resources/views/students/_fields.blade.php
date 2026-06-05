{{-- Shared fields for the create + edit student forms. --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div class="sm:col-span-2">
        <label class="block text-xs font-semibold text-slate-700 mb-1">Name <span class="text-rose-500">*</span></label>
        <input type="text" name="name" required maxlength="255"
               placeholder="Student full name"
               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800 placeholder-slate-400">
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Mobile <span class="text-rose-500">*</span></label>
        <input type="tel" name="mobile" required inputmode="numeric" pattern="[0-9]{10,15}" maxlength="15"
               placeholder="10-digit mobile"
               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800 placeholder-slate-400">
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
        <input type="email" name="email" maxlength="255"
               placeholder="student@example.com"
               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800 placeholder-slate-400">
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Admission No</label>
        <input type="text" name="admission_no" maxlength="50"
               placeholder="e.g. 001"
               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800 placeholder-slate-400">
    </div>

    <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Class</label>
        <input type="text" name="class_name" maxlength="50"
               placeholder="e.g. Class 5"
               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800 placeholder-slate-400">
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
        <label class="block text-xs font-semibold text-slate-700 mb-1">Parent / Guardian</label>
        <input type="text" name="parent_name" maxlength="255"
               placeholder="Parent name"
               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800 placeholder-slate-400">
    </div>

    <div class="sm:col-span-2">
        <label class="block text-xs font-semibold text-slate-700 mb-1">Address</label>
        <textarea name="address" rows="2" maxlength="1000"
                  placeholder="Residential address"
                  class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm text-slate-800 placeholder-slate-400"></textarea>
    </div>

    <label class="sm:col-span-2 inline-flex items-center gap-2 select-none">
        <input type="checkbox" name="active" value="1" checked
               class="w-4 h-4 rounded border-slate-300 text-pink-600 focus:ring-pink-300/60">
        <span class="text-sm text-slate-700">Active</span>
    </label>
</div>
