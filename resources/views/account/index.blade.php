@extends('layouts.admin')

@section('title', 'My Account - SSB Education')

@php
    $user = auth()->user();
    $activeTab = in_array(request('tab'), ['profile', 'edit', 'password'], true) ? request('tab') : 'profile';

    $fields = [
        ['key' => 'name',    'label' => 'Name',    'type' => 'text',  'placeholder' => 'Your name'],
        ['key' => 'email',   'label' => 'Email',   'type' => 'email', 'placeholder' => 'you@example.com'],
        ['key' => 'mobile',  'label' => 'Mobile',  'type' => 'tel',   'placeholder' => '10-digit mobile'],
    ];

    $bankFields = [
        ['key' => 'bank_name',           'label' => 'Bank Name',      'type' => 'text', 'placeholder' => 'e.g. State Bank of India'],
        ['key' => 'bank_branch',         'label' => 'Branch',         'type' => 'text', 'placeholder' => 'Branch name'],
        ['key' => 'bank_ifsc',           'label' => 'IFSC',           'type' => 'text', 'placeholder' => 'IFSC code'],
        ['key' => 'bank_account_number', 'label' => 'Account Number', 'type' => 'text', 'placeholder' => 'Account number'],
        ['key' => 'bank_holder_name',    'label' => 'Account Holder', 'type' => 'text', 'placeholder' => 'Name as per bank'],
    ];

    $tabs = ['profile' => 'Profile', 'edit' => 'Edit', 'password' => 'Change Password'];
@endphp

@section('admin-header')
<div class="sticky top-0 z-20 bg-white border-b border-slate-200">
    <div class="px-6 lg:px-10 py-3 flex flex-wrap items-baseline gap-x-4 gap-y-1 border-b border-slate-100">
        <h2 class="text-base font-bold text-slate-800">My Account</h2>
        <p class="text-xs text-slate-500">View your details, update your info &amp; change your password</p>
    </div>
    <div class="px-4 lg:px-8 flex gap-0 overflow-x-auto" role="tablist">
        @foreach ($tabs as $key => $label)
            <button type="button"
                    role="tab"
                    data-tab="{{ $key }}"
                    class="account-tab-btn relative px-3 sm:px-4 py-2.5 text-sm font-medium whitespace-nowrap transition
                           text-slate-500 hover:text-pink-600
                           data-[active=true]:text-pink-600 data-[active=true]:font-semibold">
                {{ $label }}
                <span class="account-tab-indicator absolute left-3 right-3 sm:left-4 sm:right-4 bottom-0 h-0.5 rounded-full bg-pink-500 scale-x-0 transition-transform origin-center"></span>
            </button>
        @endforeach
    </div>
</div>
@endsection

@section('admin')
<div class="bg-white rounded-xl border border-slate-200 p-6 sm:p-8">

    {{-- PROFILE (read-only) --}}
    <div id="panel-profile" class="account-tab-panel space-y-8">
        <div>
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-5 rounded-full bg-gradient-to-b from-fuchsia-500 to-pink-500"></div>
                <h3 class="text-base font-bold text-slate-800">My Details</h3>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-8">
                @foreach ($fields as $f)
                    <div>
                        <dt class="text-[11px] font-semibold text-pink-500 uppercase tracking-wider">{{ $f['label'] }}</dt>
                        <dd class="mt-1 text-slate-800 break-words">{{ $user->{$f['key']} ?: '—' }}</dd>
                    </div>
                @endforeach
                <div class="sm:col-span-2">
                    <dt class="text-[11px] font-semibold text-pink-500 uppercase tracking-wider">Address</dt>
                    <dd class="mt-1 text-slate-800 whitespace-pre-line">{{ $user->address ?: '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="border-t border-slate-100 pt-8">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-1 h-5 rounded-full bg-gradient-to-b from-emerald-500 to-teal-500"></div>
                <h3 class="text-base font-bold text-slate-800">Bank Details</h3>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-8">
                @foreach ($bankFields as $f)
                    <div>
                        <dt class="text-[11px] font-semibold text-emerald-500 uppercase tracking-wider">{{ $f['label'] }}</dt>
                        <dd class="mt-1 text-slate-800 break-words">{{ $user->{$f['key']} ?: '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>

    {{-- EDIT --}}
    <div id="panel-edit" class="account-tab-panel hidden">
        <form method="POST" action="{{ route('account.update.details') }}" class="space-y-8">
            @csrf

            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-5 rounded-full bg-gradient-to-b from-fuchsia-500 to-pink-500"></div>
                    <h3 class="text-base font-bold text-slate-800">My Details</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @foreach ($fields as $f)
                        <div>
                            <label for="{{ $f['key'] }}" class="block text-sm font-semibold text-slate-700 mb-1.5">{{ $f['label'] }}</label>
                            <input id="{{ $f['key'] }}"
                                   name="{{ $f['key'] }}"
                                   type="{{ $f['type'] }}"
                                   value="{{ old($f['key'], $user->{$f['key']}) }}"
                                   placeholder="{{ $f['placeholder'] }}"
                                   class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
                            @error($f['key'])<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                    <div class="sm:col-span-2">
                        <label for="address" class="block text-sm font-semibold text-slate-700 mb-1.5">Address</label>
                        <textarea id="address" name="address" rows="3"
                                  placeholder="Your address"
                                  class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">{{ old('address', $user->address) }}</textarea>
                        @error('address')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-8">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-5 rounded-full bg-gradient-to-b from-emerald-500 to-teal-500"></div>
                    <h3 class="text-base font-bold text-slate-800">Bank Details</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @foreach ($bankFields as $f)
                        <div>
                            <label for="{{ $f['key'] }}" class="block text-sm font-semibold text-slate-700 mb-1.5">{{ $f['label'] }}</label>
                            <input id="{{ $f['key'] }}"
                                   name="{{ $f['key'] }}"
                                   type="{{ $f['type'] }}"
                                   value="{{ old($f['key'], $user->{$f['key']}) }}"
                                   placeholder="{{ $f['placeholder'] }}"
                                   class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-emerald-300/60 focus:border-emerald-300/60 outline-none transition text-slate-800 placeholder-slate-400">
                            @error($f['key'])<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-fuchsia-500/90 via-pink-500/90 to-rose-500/90 hover:from-fuchsia-500 hover:via-pink-500 hover:to-rose-500 text-white text-sm font-semibold shadow-lg shadow-pink-500/20 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- CHANGE PASSWORD --}}
    <div id="panel-password" class="account-tab-panel hidden">
        <form method="POST" action="{{ route('account.update.password') }}" class="max-w-md mx-auto space-y-5">
            @csrf

            <div>
                <label for="current_password" class="block text-sm font-semibold text-slate-700 mb-1.5">Old Password</label>
                <div class="relative">
                    <input id="current_password" name="current_password" type="password" required
                           placeholder="Enter your current password"
                           class="w-full px-4 pr-12 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
                    <button type="button" data-pw-toggle="current_password"
                            class="pw-toggle-btn absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-pink-500/80">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.46 12C3.73 7.94 7.52 5 12 5c4.48 0 8.27 2.94 9.54 7-1.27 4.06-5.06 7-9.54 7-4.48 0-8.27-2.94-9.54-7z"/>
                        </svg>
                    </button>
                </div>
                @error('current_password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">New Password</label>
                <div class="relative">
                    <input id="password" name="password" type="password" required
                           placeholder="Enter a new password"
                           class="w-full px-4 pr-12 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
                    <button type="button" data-pw-toggle="password"
                            class="pw-toggle-btn absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-pink-500/80">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.46 12C3.73 7.94 7.52 5 12 5c4.48 0 8.27 2.94 9.54 7-1.27 4.06-5.06 7-9.54 7-4.48 0-8.27-2.94-9.54-7z"/>
                        </svg>
                    </button>
                </div>
                <p class="mt-1.5 text-xs text-slate-500">8-16 characters, with at least one letter and one special character.</p>
                @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm New Password</label>
                <div class="relative">
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           placeholder="Re-enter the new password"
                           class="w-full px-4 pr-12 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
                    <button type="button" data-pw-toggle="password_confirmation"
                            class="pw-toggle-btn absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-pink-500/80">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.46 12C3.73 7.94 7.52 5 12 5c4.48 0 8.27 2.94 9.54 7-1.27 4.06-5.06 7-9.54 7-4.48 0-8.27-2.94-9.54-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="w-full py-3 rounded-xl bg-gradient-to-r from-fuchsia-500/90 via-pink-500/90 to-rose-500/90 hover:from-fuchsia-500 hover:via-pink-500 hover:to-rose-500 text-white text-sm font-semibold shadow-lg shadow-pink-500/20 transition">
                    Change Password
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    (function () {
        const buttons = document.querySelectorAll('.account-tab-btn');
        const panels  = document.querySelectorAll('.account-tab-panel');
        const validTabs = ['profile', 'edit', 'password'];

        function setTab(name) {
            if (!validTabs.includes(name)) name = 'profile';
            buttons.forEach(b => {
                const active = b.dataset.tab === name;
                b.dataset.active = active ? 'true' : 'false';
                const ind = b.querySelector('.account-tab-indicator');
                if (ind) ind.classList.toggle('scale-x-100', active);
                if (ind) ind.classList.toggle('scale-x-0', !active);
            });
            panels.forEach(p => p.classList.toggle('hidden', p.id !== 'panel-' + name));
            const url = new URL(window.location);
            if (name === 'profile') url.searchParams.delete('tab');
            else url.searchParams.set('tab', name);
            window.history.replaceState({}, '', url);
        }

        buttons.forEach(b => b.addEventListener('click', () => setTab(b.dataset.tab)));

        document.querySelectorAll('.pw-toggle-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = document.getElementById(btn.dataset.pwToggle);
                if (!target) return;
                target.type = target.type === 'password' ? 'text' : 'password';
            });
        });

        const initial = @json($activeTab);
        const hasErrors = @json($errors->any());
        let startTab = initial;

        if (hasErrors) {
            const errorKeys = @json(array_keys($errors->getMessages()));
            if (errorKeys.some(k => ['current_password', 'password'].includes(k))) startTab = 'password';
            else if (errorKeys.some(k => ['name', 'email', 'mobile', 'address'].includes(k) || k.startsWith('bank_'))) startTab = 'edit';
        }
        setTab(startTab);
    })();
</script>
@endsection
