@extends('layouts.app')

@section('content')
@php
    $nav = [
        ['label' => 'Dashboard',     'href' => route('dashboard'),       'route' => 'dashboard',  'icon' => 'grid'],
        ['label' => 'Master Data',   'href' => '#',                      'route' => null,         'icon' => 'database'],
        ['label' => 'Users',         'href' => '#',                      'route' => null,         'icon' => 'users'],
        ['label' => 'Students',      'href' => '#',                      'route' => null,         'icon' => 'graduation'],
        ['label' => 'Announcements', 'href' => '#',                      'route' => null,         'icon' => 'megaphone'],
        ['label' => 'Accounts',      'href' => '#',                      'route' => null,         'icon' => 'cards'],
        ['label' => 'Wallet',        'href' => '#',                      'route' => null,         'icon' => 'wallet'],
        ['label' => 'Profile',       'href' => route('profile.index'),   'route' => 'profile.*',  'icon' => 'user'],
    ];

    $icons = [
        'grid'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>',
        'database'   => '<ellipse cx="12" cy="6" rx="8" ry="3" stroke-linecap="round" stroke-linejoin="round"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 6v6c0 1.66 3.58 3 8 3s8-1.34 8-3V6M4 12v6c0 1.66 3.58 3 8 3s8-1.34 8-3v-6"/>',
        'users'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M16 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
        'graduation' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>',
        'megaphone'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>',
        'cards'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/>',
        'wallet'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12V7H5a2 2 0 010-4h14v4M3 5v14a2 2 0 002 2h16v-5M18 12a2 2 0 100 4h3v-4h-3z"/>',
        'user'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
        'logout'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>',
        'arrowLeft'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>',
        'search'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>',
    ];

    $walletAmount = 3260;
@endphp

<div class="h-screen overflow-hidden flex bg-gradient-to-br from-slate-50 via-pink-50/40 to-slate-50">

    {{-- SIDEBAR --}}
    <aside class="hidden md:flex md:w-64 bg-white border-r border-slate-200 flex-col">
        <div class="px-6 py-6 border-b border-slate-100 flex flex-col items-center text-center">
            <img src="{{ $logoUrl }}" alt="SSB Education" class="w-20 h-20 object-contain drop-shadow mb-2">
            <div class="font-extrabold text-slate-800 leading-tight text-base">SSB EDUCATION</div>
        </div>

        <div class="px-6 pt-5 pb-2 text-[11px] font-semibold tracking-[0.2em] text-slate-400 uppercase">
            Dashboard
        </div>

        <nav class="flex-1 px-3 pb-4 space-y-1 overflow-y-auto">
            @foreach ($nav as $item)
                @php $active = $item['route'] && request()->routeIs($item['route']); @endphp
                <a href="{{ $item['href'] }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                          {{ $active
                                ? 'bg-pink-50 text-pink-600 font-semibold ring-1 ring-pink-100'
                                : 'text-slate-600 hover:bg-pink-50/60 hover:text-pink-600' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        {!! $icons[$item['icon']] !!}
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach

            <button type="button" onclick="openLogoutModal()" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    {!! $icons['logout'] !!}
                </svg>
                Logout
            </button>
        </nav>
    </aside>

    {{-- MAIN --}}
    <main class="flex-1 flex flex-col overflow-hidden">

        {{-- TOPBAR --}}
        <header class="bg-white border-b border-slate-200 px-4 lg:px-6 py-3 flex items-center gap-3 lg:gap-4">

            <button type="button" onclick="history.back()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 text-sm font-medium transition shadow-sm shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    {!! $icons['arrowLeft'] !!}
                </svg>
                Back
            </button>

            <div class="hidden md:flex items-center gap-2 shrink-0">
                <span class="text-base text-slate-700">Welcome! SSB EDUCATION ADMIN</span>
                <span class="text-xs font-semibold text-pink-600 bg-pink-50 border border-pink-100 px-2 py-0.5 rounded-md">2026–27</span>
            </div>

            <div class="flex-1 max-w-xl mx-auto">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            {!! $icons['search'] !!}
                        </svg>
                    </div>
                    <input type="text" placeholder="Search pages..."
                           class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-full text-sm placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 transition">
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm font-semibold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        {!! $icons['wallet'] !!}
                    </svg>
                    ₹{{ number_format($walletAmount) }}
                </div>

                <a href="{{ route('profile.index') }}" title="Profile"
                   class="w-10 h-10 rounded-full bg-pink-50 border border-pink-100 text-pink-600 hover:bg-pink-100 hover:text-pink-700 flex items-center justify-center transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        {!! $icons['user'] !!}
                    </svg>
                </a>

                <button type="button" onclick="openLogoutModal()" title="Logout"
                        class="w-10 h-10 rounded-full bg-rose-50 border border-rose-100 text-rose-600 hover:bg-rose-100 hover:text-rose-700 flex items-center justify-center transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        {!! $icons['logout'] !!}
                    </svg>
                </button>
            </div>
        </header>

        {{-- PAGE BODY --}}
        <div class="flex-1 overflow-y-auto p-6 lg:p-10 space-y-8">
            @if (session('status'))
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            @yield('admin')
        </div>
    </main>
</div>

{{-- LOGOUT CONFIRMATION MODAL --}}
<div id="logoutModal" class="hidden fixed inset-0 z-50 items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" onclick="if(event.target===this)closeLogoutModal()">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 ring-1 ring-slate-100">
        <div class="flex items-start gap-4 mb-5">
            <div class="w-11 h-11 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 ring-1 ring-rose-100">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    {!! $icons['logout'] !!}
                </svg>
            </div>
            <div class="pt-0.5">
                <h3 class="font-bold text-slate-800 text-base">Confirm Logout</h3>
                <p class="text-sm text-slate-500 mt-1 leading-snug">Are you sure you want to log out of your account?</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="closeLogoutModal()"
                    class="flex-1 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition">
                Cancel
            </button>
            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf
                <button type="submit"
                        class="w-full py-2.5 rounded-xl bg-gradient-to-r from-rose-500 to-rose-600 hover:from-rose-600 hover:to-rose-700 text-white text-sm font-semibold shadow-md shadow-rose-500/20 transition">
                    Yes, Logout
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function openLogoutModal() {
        const m = document.getElementById('logoutModal');
        m.classList.remove('hidden');
        m.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    function closeLogoutModal() {
        const m = document.getElementById('logoutModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeLogoutModal();
    });
</script>
@endsection
