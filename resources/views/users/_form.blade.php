@php
    /** @var \App\Models\User|null $user */
    $user = $user ?? null;
    $passwordRequired = $user === null;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    <div>
        <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Name <span class="text-rose-500">*</span></label>
        <input id="name" name="name" type="text" required
               value="{{ old('name', $user?->name) }}"
               placeholder="Full name"
               class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
        @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="mobile" class="block text-sm font-semibold text-slate-700 mb-1.5">Mobile <span class="text-rose-500">*</span></label>
        <input id="mobile" name="mobile" type="tel" required
               value="{{ old('mobile', $user?->mobile) }}"
               placeholder="10-digit mobile"
               class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
        @error('mobile')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
        <input id="email" name="email" type="email"
               value="{{ old('email', $user?->email) }}"
               placeholder="user@example.com"
               class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
        @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">
            Password @if ($passwordRequired)<span class="text-rose-500">*</span>@endif
        </label>
        <input id="password" name="password" type="password" {{ $passwordRequired ? 'required' : '' }}
               placeholder="{{ $passwordRequired ? 'Login password' : 'Leave blank to keep current' }}"
               class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
        @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="address" class="block text-sm font-semibold text-slate-700 mb-1.5">Address</label>
        <textarea id="address" name="address" rows="3"
                  placeholder="Full address"
                  class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">{{ old('address', $user?->address) }}</textarea>
        @error('address')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        @php $isActive = (bool) old('active', $user?->active ?? true); @endphp
        <label class="inline-flex items-center gap-3 cursor-pointer select-none">
            <span class="relative">
                <input type="hidden" name="active" value="0">
                <input id="active" name="active" type="checkbox" value="1" {{ $isActive ? 'checked' : '' }}
                       class="peer sr-only">
                <span class="block w-11 h-6 rounded-full bg-slate-200 peer-checked:bg-emerald-500 transition"></span>
                <span class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
            </span>
            <span class="text-sm font-semibold text-slate-700">Active</span>
            <span class="text-xs text-slate-500">— inactive users cannot login.</span>
        </label>
    </div>
</div>

<div class="flex justify-end gap-2 pt-2">
    <a href="{{ route('users.index') }}"
       class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition">
        Cancel
    </a>
    <button type="submit"
            class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-fuchsia-500/90 via-pink-500/90 to-rose-500/90 hover:from-fuchsia-500 hover:via-pink-500 hover:to-rose-500 text-white text-sm font-semibold shadow-lg shadow-pink-500/20 transition">
        {{ $submitLabel ?? 'Save' }}
    </button>
</div>
