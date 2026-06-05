@extends('layouts.admin')

@section('title', 'Profile - SSB Education')

@section('admin')
@php
    $platformFields = [
        ['key' => 'platform_name',       'label' => 'Name',             'type' => 'text',  'placeholder' => 'Organization name'],
        ['key' => 'platform_email',      'label' => 'Email',            'type' => 'email', 'placeholder' => 'admin@example.com'],
        ['key' => 'platform_mobile',     'label' => 'Mobile',           'type' => 'tel',   'placeholder' => '10-digit mobile'],
        ['key' => 'platform_alt_mobile', 'label' => 'Alternate Mobile', 'type' => 'tel',   'placeholder' => 'Backup contact number'],
        ['key' => 'website',             'label' => 'Website',          'type' => 'url',   'placeholder' => 'https://...'],
        ['key' => 'owner',               'label' => 'Owner',            'type' => 'text',  'placeholder' => 'Owner full name'],
    ];

    $bankFields = [
        ['key' => 'bank_name',           'label' => 'Bank Name',        'type' => 'text', 'placeholder' => 'e.g. State Bank of India'],
        ['key' => 'bank_branch',         'label' => 'Branch',           'type' => 'text', 'placeholder' => 'Branch name'],
        ['key' => 'bank_ifsc',           'label' => 'IFSC',             'type' => 'text', 'placeholder' => 'IFSC code'],
        ['key' => 'bank_account_number', 'label' => 'Account Number',   'type' => 'text', 'placeholder' => 'Account number'],
        ['key' => 'bank_holder_name',    'label' => 'Account Holder',   'type' => 'text', 'placeholder' => 'Name as per bank'],
    ];

    $activeTab = in_array(request('tab'), ['profile', 'edit', 'password'], true) ? request('tab') : 'profile';
@endphp

{{-- PAGE HEADER --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Organization Profile</h1>
        <p class="text-sm text-slate-500 mt-1">Manage your platform details, bank info & login password</p>
    </div>
</div>

{{-- TABS + CONTENT CARD --}}
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

    {{-- Tab triggers --}}
    <div class="border-b border-slate-100 px-2 sm:px-4 flex gap-1 overflow-x-auto" role="tablist">
        @foreach (['profile' => 'Profile', 'edit' => 'Edit', 'password' => 'Change Password'] as $key => $label)
            <button type="button"
                    role="tab"
                    data-tab="{{ $key }}"
                    class="profile-tab-btn relative px-4 sm:px-6 py-4 text-sm font-semibold whitespace-nowrap transition
                           text-slate-500 hover:text-pink-600
                           data-[active=true]:text-pink-600">
                {{ $label }}
                <span class="profile-tab-indicator absolute left-2 right-2 sm:left-4 sm:right-4 bottom-0 h-0.5 rounded-full bg-pink-500 scale-x-0 transition-transform origin-center"></span>
            </button>
        @endforeach
    </div>

    {{-- Compact brand strip — uses the same logo as the sidebar --}}
    <div class="flex items-center gap-4 px-6 sm:px-8 py-4 border-b border-slate-100 bg-gradient-to-r from-pink-50/60 via-rose-50/30 to-transparent">
        <div class="w-14 h-14 rounded-full bg-white ring-2 ring-pink-100 shadow-sm flex items-center justify-center overflow-hidden shrink-0">
            <img src="{{ $logoDataUri }}" alt="Logo"
                 width="56" height="56" decoding="sync" fetchpriority="high"
                 class="w-full h-full object-contain p-1">
        </div>
        <div class="min-w-0">
            <h2 class="text-base font-extrabold text-slate-800 leading-tight truncate">
                {{ $settings->platform_name ?: 'SSB Education' }}
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Organization profile</p>
        </div>
    </div>

    {{-- Panels --}}
    <div class="p-6 sm:p-8">

        {{-- PROFILE (read-only) --}}
        <div id="panel-profile" class="profile-tab-panel space-y-8">
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1 h-5 rounded-full bg-gradient-to-b from-fuchsia-500 to-pink-500"></div>
                    <h3 class="text-base font-bold text-slate-800">Platform Details</h3>
                </div>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-8">
                    @foreach ($platformFields as $f)
                        <div>
                            <dt class="text-[11px] font-semibold text-pink-500 uppercase tracking-wider">{{ $f['label'] }}</dt>
                            <dd class="mt-1 text-slate-800 break-words">
                                {{ $settings->{$f['key']} ?: '—' }}
                            </dd>
                        </div>
                    @endforeach
                    <div class="sm:col-span-2">
                        <dt class="text-[11px] font-semibold text-pink-500 uppercase tracking-wider">Address</dt>
                        <dd class="mt-1 text-slate-800 whitespace-pre-line">
                            {{ $settings->address ?: '—' }}
                        </dd>
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
                            <dd class="mt-1 text-slate-800 break-words">
                                {{ $settings->{$f['key']} ?: '—' }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>

        {{-- EDIT --}}
        <div id="panel-edit" class="profile-tab-panel hidden">
            <form method="POST" action="{{ route('profile.update.details') }}" class="space-y-8">
                @csrf

                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-1 h-5 rounded-full bg-gradient-to-b from-fuchsia-500 to-pink-500"></div>
                        <h3 class="text-base font-bold text-slate-800">Platform Details</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        @foreach ($platformFields as $f)
                            <div>
                                <label for="{{ $f['key'] }}" class="block text-sm font-semibold text-slate-700 mb-1.5">{{ $f['label'] }}</label>
                                <input id="{{ $f['key'] }}"
                                       name="{{ $f['key'] }}"
                                       type="{{ $f['type'] }}"
                                       value="{{ old($f['key'], $settings->{$f['key']}) }}"
                                       placeholder="{{ $f['placeholder'] }}"
                                       class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
                                @error($f['key'])<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                        <div class="sm:col-span-2">
                            <label for="address" class="block text-sm font-semibold text-slate-700 mb-1.5">Address</label>
                            <textarea id="address" name="address" rows="3"
                                      placeholder="Full address"
                                      class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">{{ old('address', $settings->address) }}</textarea>
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
                                       value="{{ old($f['key'], $settings->{$f['key']}) }}"
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
        <div id="panel-password" class="profile-tab-panel hidden">
            <form method="POST" action="{{ route('profile.update.password') }}" class="max-w-md mx-auto space-y-5">
                @csrf

                <div>
                    <label for="current_password" class="block text-sm font-semibold text-slate-700 mb-1.5">Old Password</label>
                    <input id="current_password" name="current_password" type="password" required
                           placeholder="Enter your current password"
                           class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
                    @error('current_password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">New Password</label>
                    <input id="password" name="password" type="password" required
                           placeholder="Enter a new password"
                           class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
                    <p class="mt-1.5 text-xs text-slate-500">8-16 characters, with at least one letter and one special character.</p>
                    @error('password')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           placeholder="Re-enter the new password"
                           class="w-full px-4 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-slate-800 placeholder-slate-400">
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
</div>

<script>
    (function () {
        const buttons = document.querySelectorAll('.profile-tab-btn');
        const panels  = document.querySelectorAll('.profile-tab-panel');
        const validTabs = ['profile', 'edit', 'password'];

        function setTab(name) {
            if (!validTabs.includes(name)) name = 'profile';
            buttons.forEach(b => {
                const active = b.dataset.tab === name;
                b.dataset.active = active ? 'true' : 'false';
                const ind = b.querySelector('.profile-tab-indicator');
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

        const initial = @json($activeTab);
        const hasErrors = @json($errors->any());
        let startTab = initial;

        if (hasErrors) {
            const errorKeys = @json(array_keys($errors->getMessages()));
            if (errorKeys.some(k => ['current_password', 'password'].includes(k))) startTab = 'password';
            else if (errorKeys.some(k => k.startsWith('platform_') || k === 'address' || k === 'website' || k === 'owner' || k.startsWith('bank_'))) startTab = 'edit';
        }
        setTab(startTab);
    })();
</script>
@endsection
