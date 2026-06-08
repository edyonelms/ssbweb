@php
    /** @var string $mode 'create' or 'edit' */
    $passwordRequired = $mode === 'create';
@endphp

<div>
    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Photo</label>
    <div class="flex items-center gap-4">
        <img data-avatar-preview src="" alt=""
             class="w-16 h-16 rounded-full object-cover ring-1 ring-slate-200 hidden">
        <label class="flex-1 px-4 py-2.5 rounded-xl bg-slate-50 border border-dashed border-slate-300 text-sm text-slate-500 hover:bg-slate-100 cursor-pointer transition">
            <span>Choose image</span>
            <input type="file" name="avatar" accept="image/*" data-avatar-input class="hidden">
        </label>
    </div>
    @error('avatar')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
</div>

<div>
    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Name <span class="text-rose-500">*</span></label>
    <input name="name" type="text" required
           autocomplete="off" autocorrect="off" spellcheck="false"
           value="{{ old('panel_mode') === $mode ? old('name') : '' }}"
           placeholder="Full name"
           class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
    @if (old('panel_mode') === $mode) @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
</div>

<div>
    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Mobile <span class="text-rose-500">*</span></label>
    <input name="mobile" type="tel" required
           autocomplete="off"
           value="{{ old('panel_mode') === $mode ? old('mobile') : '' }}"
           placeholder="10-digit mobile"
           class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
    @if (old('panel_mode') === $mode) @error('mobile')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
</div>

<div>
    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
    <input name="email" type="email"
           autocomplete="off" autocorrect="off" spellcheck="false"
           value="{{ old('panel_mode') === $mode ? old('email') : '' }}"
           placeholder="user@example.com"
           class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
    @if (old('panel_mode') === $mode) @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
</div>

<div>
    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
        Password @if ($passwordRequired)<span class="text-rose-500">*</span>@endif
    </label>
    <input name="password" type="password" {{ $passwordRequired ? 'required' : '' }}
           autocomplete="new-password"
           placeholder="{{ $passwordRequired ? 'Login password' : 'Leave blank to keep current' }}"
           class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
    @if (old('panel_mode') === $mode) @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
</div>

<div>
    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Address</label>
    <textarea name="address" rows="3"
              placeholder="Full address"
              class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">{{ old('panel_mode') === $mode ? old('address') : '' }}</textarea>
    @if (old('panel_mode') === $mode) @error('address')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
</div>

<div>
    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Organization Name <span class="text-xs font-normal text-slate-400">(optional)</span></label>
    <input name="organization_name" type="text"
           autocomplete="off" autocorrect="off" spellcheck="false"
           value="{{ old('panel_mode') === $mode ? old('organization_name') : '' }}"
           placeholder="e.g. Bright Future Coaching"
           class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
    @if (old('panel_mode') === $mode) @error('organization_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
</div>

<div>
    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Organization Details <span class="text-xs font-normal text-slate-400">(optional)</span></label>
    <textarea name="organization_details" rows="3"
              placeholder="Address, GST, contact person, etc."
              class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">{{ old('panel_mode') === $mode ? old('organization_details') : '' }}</textarea>
    @if (old('panel_mode') === $mode) @error('organization_details')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror @endif
</div>

<div>
    @php
        $isActive = old('panel_mode') === $mode
            ? (bool) old('active', $mode === 'create' ? true : false)
            : true;
    @endphp
    <label class="inline-flex items-center gap-3 cursor-pointer select-none">
        <span class="relative">
            <input type="hidden" name="active" value="0">
            <input name="active" type="checkbox" value="1" {{ $isActive ? 'checked' : '' }}
                   class="peer sr-only">
            <span class="block w-11 h-6 rounded-full bg-slate-200 peer-checked:bg-emerald-500 transition"></span>
            <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
        </span>
        <span class="text-sm font-semibold text-slate-700">Active</span>
        <span class="text-xs text-slate-500">— inactive users cannot login.</span>
    </label>
</div>
