@extends('layouts.admin')

@section('title', 'Dashboard - SSB Education')

@section('admin')
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
@endsection
