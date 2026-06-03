@extends('layouts.app')

@section('title', 'Login - SSB Education')

@push('head')
@endpush

@section('content')
<div class="h-screen w-full overflow-hidden flex bg-gradient-to-br from-pink-50 via-rose-50 to-fuchsia-100">

    {{-- LEFT ILLUSTRATION SIDE --}}
    <div class="hidden lg:flex w-1/2 items-center justify-center p-10 relative overflow-hidden">
        {{-- ambient blobs --}}
        <div class="absolute -top-24 -left-24 w-80 h-80 rounded-full bg-pink-300/40 blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-24 -right-24 w-[28rem] h-[28rem] rounded-full bg-fuchsia-300/40 blur-3xl"></div>
        <div class="absolute top-1/3 right-10 w-40 h-40 rounded-full bg-rose-200/40 blur-2xl"></div>

        {{-- floating accent shapes --}}
        <div class="absolute top-16 right-20 w-8 h-8 rounded-lg bg-gradient-to-br from-pink-400 to-rose-400 rotate-12 shadow-lg shadow-pink-500/30"></div>
        <div class="absolute bottom-24 left-20 w-6 h-6 rounded-full bg-gradient-to-br from-fuchsia-400 to-purple-400 shadow-lg shadow-fuchsia-500/30"></div>
        <div class="absolute top-32 left-32 w-4 h-4 rounded-full bg-rose-400 shadow-md"></div>

        <div class="relative w-full max-w-md">
            {{-- main glass card --}}
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] shadow-2xl shadow-pink-500/20 p-10 ring-1 ring-white relative z-10">
                {{-- logo glow ring --}}
                <div class="relative w-44 h-44 mx-auto mb-8">
                    <div class="absolute inset-0 rounded-full bg-gradient-to-br from-pink-200 via-rose-200 to-fuchsia-200 blur-xl opacity-70"></div>
                    <div class="absolute inset-2 rounded-full bg-gradient-to-br from-white to-pink-50 shadow-inner ring-4 ring-white"></div>
                    <img src="{{ asset('images/logo.png') }}" alt="SSB Education" class="relative w-full h-full object-contain p-4 drop-shadow-lg">
                </div>

                <h2 class="text-2xl font-extrabold text-center bg-gradient-to-r from-fuchsia-600 via-pink-600 to-rose-600 bg-clip-text text-transparent mb-2">
                    Vidya Daanam Param Daanam
                </h2>
                <p class="text-sm text-center text-slate-500 mb-6">Knowledge is the supreme gift</p>

                {{-- feature pills --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-3 bg-gradient-to-r from-pink-50 to-rose-50 px-4 py-3 rounded-2xl border border-pink-100">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center text-white shadow-md shadow-pink-500/30">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Quality Education</span>
                    </div>
                    <div class="flex items-center gap-3 bg-gradient-to-r from-fuchsia-50 to-pink-50 px-4 py-3 rounded-2xl border border-fuchsia-100">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-fuchsia-500 to-pink-500 flex items-center justify-center text-white shadow-md shadow-fuchsia-500/30">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Trusted by Students</span>
                    </div>
                    <div class="flex items-center gap-3 bg-gradient-to-r from-rose-50 to-fuchsia-50 px-4 py-3 rounded-2xl border border-rose-100">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-rose-500 to-fuchsia-500 flex items-center justify-center text-white shadow-md shadow-rose-500/30">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-700">Learn at Your Pace</span>
                    </div>
                </div>
            </div>

            {{-- depth shadow card --}}
            <div class="absolute -bottom-5 -right-5 w-full h-full rounded-[2.5rem] bg-gradient-to-br from-pink-300 to-fuchsia-300 -z-0"></div>
        </div>
    </div>

    {{-- RIGHT FORM SIDE --}}
    <div class="w-full lg:w-1/2 bg-white flex items-center justify-center px-6 py-6 relative overflow-y-auto">
        <div class="w-full max-w-md">
            <div class="text-center mb-6">
                <img src="{{ asset('images/logo.png') }}" alt="SSB Education" class="w-20 h-20 mx-auto mb-2 drop-shadow-md object-contain">
                <div class="text-xs font-semibold tracking-[0.3em] text-pink-500">SSB EDUCATION</div>
            </div>

            <h1 class="text-4xl font-extrabold text-slate-800 text-center mb-2">Welcome Back!</h1>
            <p class="text-slate-500 text-center mb-6">Login to continue to your dashboard</p>

            @if ($errors->any())
                <div class="mb-5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="mobile" class="block text-sm font-semibold text-slate-700 mb-1.5">Mobile Number</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-pink-400">
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
                            class="w-full pl-12 pr-4 py-3 bg-pink-50/50 border border-pink-100 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 transition outline-none text-slate-800 placeholder-slate-400"
                        >
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-pink-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c1.1 0 2-.9 2-2V7a2 2 0 10-4 0v2c0 1.1.9 2 2 2zm6 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2v-6a2 2 0 012-2h8a2 2 0 012 2z"/>
                            </svg>
                        </div>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            placeholder="Enter your password"
                            class="w-full pl-12 pr-12 py-3 bg-pink-50/50 border border-pink-100 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-400 focus:border-pink-400 transition outline-none text-slate-800 placeholder-slate-400"
                        >
                        <button type="button" onclick="const p=document.getElementById('password');p.type=p.type==='password'?'text':'password'" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-pink-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.46 12C3.73 7.94 7.52 5 12 5c4.48 0 8.27 2.94 9.54 7-1.27 4.06-5.06 7-9.54 7-4.48 0-8.27-2.94-9.54-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-fuchsia-500 via-pink-500 to-rose-500 hover:from-fuchsia-600 hover:via-pink-600 hover:to-rose-600 text-white font-semibold rounded-xl shadow-lg shadow-pink-500/30 hover:shadow-pink-500/50 transition-all transform hover:-translate-y-0.5 active:translate-y-0"
                >
                    Login
                </button>
            </form>

            <p class="text-center text-xs text-slate-400 mt-8">
                &copy; {{ date('Y') }} SSB Education. All rights reserved.
            </p>
        </div>
    </div>
</div>
@endsection
