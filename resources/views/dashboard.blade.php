@extends('layouts.app')

@section('title', 'Dashboard - SSB Education')

@section('content')
@php
    $nav = [
        ['label' => 'Quick Links',   'href' => '#', 'route' => null,        'icon' => 'bolt'],
        ['label' => 'Dashboard',     'href' => route('dashboard'), 'route' => 'dashboard', 'icon' => 'grid'],
        ['label' => 'Announcements', 'href' => '#', 'route' => null,        'icon' => 'megaphone'],
        ['label' => 'Students',      'href' => '#', 'route' => null,        'icon' => 'users'],
        ['label' => 'Accounts',      'href' => '#', 'route' => null,        'icon' => 'cards'],
        ['label' => 'Wallet',        'href' => '#', 'route' => null,        'icon' => 'wallet'],
        ['label' => 'Profile',       'href' => '#', 'route' => null,        'icon' => 'user'],
    ];

    $icons = [
        'bolt'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
        'grid'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>',
        'megaphone' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>',
        'users'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M16 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
        'cards'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/>',
        'wallet'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12V7H5a2 2 0 010-4h14v4M3 5v14a2 2 0 002 2h16v-5M18 12a2 2 0 100 4h3v-4h-3z"/>',
        'user'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
        'logout'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>',
    ];
@endphp

<div class="min-h-screen flex bg-gradient-to-br from-slate-50 via-pink-50/40 to-slate-50">
    <aside class="hidden md:flex md:w-64 bg-white border-r border-slate-200 flex-col">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
            <x-logo class="w-11 h-11 drop-shadow" />
            <div>
                <div class="font-extrabold text-slate-800 leading-tight">SSB Education</div>
                <div class="text-[11px] text-pink-500 font-medium tracking-wider">LEARNING PORTAL</div>
            </div>
        </div>

        <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
            @foreach ($nav as $item)
                @php $active = $item['route'] && request()->routeIs($item['route']); @endphp
                <a href="{{ $item['href'] }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                          {{ $active
                                ? 'bg-gradient-to-r from-fuchsia-500 via-pink-500 to-rose-500 text-white shadow-md shadow-pink-500/30'
                                : 'text-slate-600 hover:bg-pink-50 hover:text-pink-600' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        {!! $icons[$item['icon']] !!}
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="p-3 border-t border-slate-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-rose-50 hover:text-rose-600 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        {!! $icons['logout'] !!}
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white/80 backdrop-blur border-b border-slate-200 px-6 lg:px-10 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800">Dashboard</h1>
                <p class="text-sm text-slate-500">Hello {{ $user->name }}, here's what's happening today</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:block text-right">
                    <div class="text-sm font-semibold text-slate-800 leading-tight">{{ $user->name }}</div>
                    <div class="text-xs text-slate-500">{{ $user->mobile }}</div>
                </div>
                <div class="w-11 h-11 rounded-full bg-gradient-to-br from-fuchsia-500 via-pink-500 to-rose-500 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-pink-500/30">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6 lg:p-10 space-y-8">
            <div class="relative overflow-hidden rounded-3xl p-8 bg-gradient-to-r from-fuchsia-500 via-pink-500 to-rose-500 text-white shadow-xl shadow-pink-500/20">
                <div class="absolute -top-10 -right-10 w-40 h-40 rounded-full bg-white/10 blur-2xl"></div>
                <div class="absolute -bottom-10 -left-10 w-40 h-40 rounded-full bg-white/10 blur-2xl"></div>
                <div class="relative">
                    <h2 class="text-3xl font-extrabold mb-1">Welcome back, {{ $user->name }} ✨</h2>
                    <p class="text-pink-100">Aaj kuch naya seekhne ka time hai!</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-pink-100/60 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-pink-100 to-rose-100 flex items-center justify-center mb-4 text-pink-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-800">My Courses</h3>
                    <p class="text-slate-500 text-sm">Enrolled courses</p>
                    <p class="text-3xl font-extrabold text-pink-600 mt-3">0</p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-purple-100/60 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-100 to-fuchsia-100 flex items-center justify-center mb-4 text-purple-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-800">Completed</h3>
                    <p class="text-slate-500 text-sm">Finished lessons</p>
                    <p class="text-3xl font-extrabold text-purple-600 mt-3">0</p>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-teal-100/60 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-100 to-cyan-100 flex items-center justify-center mb-4 text-teal-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-800">Streak</h3>
                    <p class="text-slate-500 text-sm">Continuous days</p>
                    <p class="text-3xl font-extrabold text-teal-600 mt-3">1</p>
                </div>
            </div>

            <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-100">
                <h2 class="text-xl font-extrabold text-slate-800 mb-1">Account Details</h2>
                <p class="text-slate-500 text-sm mb-6">Your profile information</p>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-5 gap-x-8">
                    <div>
                        <dt class="text-xs font-semibold text-pink-500 uppercase tracking-wider">Name</dt>
                        <dd class="mt-1 text-slate-800 font-semibold">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-pink-500 uppercase tracking-wider">Mobile Number</dt>
                        <dd class="mt-1 text-slate-800 font-semibold">{{ $user->mobile }}</dd>
                    </div>
                    @if ($user->email)
                    <div>
                        <dt class="text-xs font-semibold text-pink-500 uppercase tracking-wider">Email</dt>
                        <dd class="mt-1 text-slate-800 font-semibold">{{ $user->email }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs font-semibold text-pink-500 uppercase tracking-wider">Member Since</dt>
                        <dd class="mt-1 text-slate-800 font-semibold">{{ $user->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </main>
</div>
@endsection
