@extends('layouts.app')

@section('title', 'Dashboard - SSB Education')

@section('content')
<div class="min-h-screen bg-slate-100">
    <nav class="bg-white shadow-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-600 to-blue-600 flex items-center justify-center">
                        <span class="text-white font-extrabold text-sm">SSB</span>
                    </div>
                    <span class="font-bold text-slate-800 text-lg">SSB Education</span>
                </div>

                <div class="flex items-center gap-4">
                    <span class="hidden sm:block text-sm text-slate-600">Hi, <span class="font-semibold text-slate-800">{{ $user->name }}</span></span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 hover:text-white hover:bg-red-600 border border-red-600 rounded-lg transition">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-2xl p-8 text-white shadow-lg mb-8">
            <h1 class="text-3xl font-bold mb-1">Welcome back, {{ $user->name }} 👋</h1>
            <p class="text-blue-100">Aaj kuch naya seekhne ke liye taiyaar ho?</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 text-lg">My Courses</h3>
                <p class="text-slate-500 text-sm mt-1">Enrolled courses dekhein</p>
                <p class="text-3xl font-bold text-indigo-600 mt-3">0</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 text-lg">Completed</h3>
                <p class="text-slate-500 text-sm mt-1">Pure ho chuke lessons</p>
                <p class="text-3xl font-bold text-green-600 mt-3">0</p>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 text-lg">Streak</h3>
                <p class="text-slate-500 text-sm mt-1">Continuous learning days</p>
                <p class="text-3xl font-bold text-orange-600 mt-3">1</p>
            </div>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-xl font-bold text-slate-800 mb-1">Account Details</h2>
            <p class="text-slate-500 text-sm mb-6">Aapki profile ki jankari</p>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-8">
                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Name</dt>
                    <dd class="mt-1 text-slate-800 font-medium">{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Mobile Number</dt>
                    <dd class="mt-1 text-slate-800 font-medium">{{ $user->mobile }}</dd>
                </div>
                @if ($user->email)
                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Email</dt>
                    <dd class="mt-1 text-slate-800 font-medium">{{ $user->email }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Member Since</dt>
                    <dd class="mt-1 text-slate-800 font-medium">{{ $user->created_at->format('d M Y') }}</dd>
                </div>
            </dl>
        </div>
    </main>
</div>
@endsection
