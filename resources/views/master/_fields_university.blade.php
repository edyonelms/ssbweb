{{-- Shared fields for the create + edit University forms. --}}
<div>
    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Type <span class="text-rose-500">*</span></label>
    <div class="grid grid-cols-2 gap-2">
        <label class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl border cursor-pointer text-sm font-semibold transition
                      border-slate-200 text-slate-600 hover:border-slate-300
                      has-[:checked]:bg-pink-50 has-[:checked]:border-pink-400 has-[:checked]:text-pink-700">
            <input type="radio" name="type" value="university" checked class="sr-only">
            University
        </label>
        <label class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl border cursor-pointer text-sm font-semibold transition
                      border-slate-200 text-slate-600 hover:border-slate-300
                      has-[:checked]:bg-pink-50 has-[:checked]:border-pink-400 has-[:checked]:text-pink-700">
            <input type="radio" name="type" value="board" class="sr-only">
            Board
        </label>
    </div>
</div>

<div>
    <label class="block text-xs font-semibold text-slate-700 mb-1">Name <span class="text-rose-500">*</span></label>
    <input type="text" name="name" required maxlength="255"
           placeholder="e.g. Mangalayatan University"
           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
</div>

<div>
    <label class="block text-xs font-semibold text-slate-700 mb-1">Address</label>
    <textarea name="address" rows="2" maxlength="1000"
              placeholder="Postal address"
              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm"></textarea>
</div>

<div class="grid grid-cols-2 gap-3">
    <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Website</label>
        <input type="text" name="website" maxlength="255"
               placeholder="https://..."
               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
    </div>
    <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Registration Fee (₹)</label>
        <input type="number" step="1" min="0" name="registration_fee"
               placeholder="0"
               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
    </div>
</div>

<div>
    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
        Image / Logo <span class="text-xs font-normal text-slate-500">(Optional · PNG/JPG, max 2MB)</span>
    </label>
    <label class="flex flex-col items-center justify-center gap-2 px-4 py-4 rounded-xl border-2 border-dashed border-slate-200 hover:border-pink-300 hover:bg-pink-50/20 cursor-pointer transition text-center">
        <img data-image-preview src="" alt="" class="hidden w-16 h-16 rounded-md object-cover">
        <svg class="w-6 h-6 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span class="text-sm font-medium text-slate-600">Click to upload image</span>
        <span data-file-name class="text-xs text-pink-600 font-semibold"></span>
        <input type="file" name="image" accept=".png,.jpg,.jpeg,.webp" data-image-input class="hidden">
    </label>
</div>
