@extends('layouts.app')

@section('title', 'Login - SSB Education')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-10 bg-gradient-to-br from-indigo-600 via-blue-700 to-slate-900">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/10 backdrop-blur ring-1 ring-white/20 mb-4">
                <span class="text-2xl font-extrabold text-white tracking-tight">SSB</span>
            </div>
            <h1 class="text-3xl font-bold text-white">SSB Education</h1>
            <p class="text-blue-100 mt-2">Apne account mein login karein</p>
        </div>

        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-slate-800 mb-1">Welcome back</h2>
            <p class="text-slate-500 text-sm mb-6">Mobile number aur password se sign in karein</p>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="mobile" class="block text-sm font-medium text-slate-700 mb-1.5">Mobile Number</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h2.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11 11 0 005.5 5.5l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z"/>
                            </svg>
                        </div>
                        <input
                            id="mobile"
                            name="mobile"
                            type="tel"
                            inputmode="numeric"
                            pattern="[0-9]{10,15}"
                            maxlength="15"
                            value="{{ old('mobile') }}"
                            required
                            autofocus
                            placeholder="10-digit mobile number"
                            class="w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none"
                        >
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                        <a href="#" class="text-xs text-indigo-600 hover:text-indigo-700">Forgot password?</a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.1 0 2-.9 2-2V7a2 2 0 10-4 0v2c0 1.1.9 2 2 2zm6 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2v-6a2 2 0 012-2h8a2 2 0 012 2z"/>
                            </svg>
                        </div>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            placeholder="Apna password daalein"
                            class="w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none"
                        >
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                    <label for="remember" class="ml-2 text-sm text-slate-600">Mujhe yaad rakhein</label>
                </div>

                <button
                    type="submit"
                    class="w-full py-2.5 px-4 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-semibold rounded-lg shadow-md transition transform hover:-translate-y-0.5 active:translate-y-0"
                >
                    Login
                </button>
            </form>

            <p class="text-center text-sm text-slate-500 mt-6">
                Naye user hain? <a href="#" class="text-indigo-600 font-medium hover:underline">Account banayein</a>
            </p>
        </div>

        <p class="text-center text-blue-100 text-xs mt-6">
            &copy; {{ date('Y') }} SSB Education. All rights reserved.
        </p>
    </div>
</div>
@endsection
