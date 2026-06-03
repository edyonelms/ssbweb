@extends('layouts.app')

@section('title', 'Login - SSB Education')

@section('content')
<div class="h-screen w-full overflow-hidden flex items-center justify-center bg-gradient-to-br from-pink-50/50 via-rose-50/40 to-fuchsia-100/40 px-4 relative">

    {{-- ambient blobs (very soft) --}}
    <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-pink-200/20 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-[28rem] h-[28rem] rounded-full bg-fuchsia-200/20 blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative">
        <div class="text-center mb-6">
            <img src="{{ asset('images/logo.png') }}" alt="SSB Education" class="w-24 h-24 mx-auto mb-2 drop-shadow-md object-contain">
            <div class="text-xs font-semibold tracking-[0.3em] text-pink-500/80">SSB EDUCATION</div>
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
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-pink-400/80">
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
                        class="w-full pl-12 pr-4 py-3 bg-pink-50/40 border border-pink-100/70 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-400/70 focus:border-pink-400/70 transition outline-none text-slate-800 placeholder-slate-400"
                    >
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-pink-400/80">
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
                        class="w-full pl-12 pr-12 py-3 bg-pink-50/40 border border-pink-100/70 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-400/70 focus:border-pink-400/70 transition outline-none text-slate-800 placeholder-slate-400"
                    >
                    <button type="button" onclick="const p=document.getElementById('password');p.type=p.type==='password'?'text':'password'" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-pink-500/80">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.46 12C3.73 7.94 7.52 5 12 5c4.48 0 8.27 2.94 9.54 7-1.27 4.06-5.06 7-9.54 7-4.48 0-8.27-2.94-9.54-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button
                type="submit"
                class="w-full py-3.5 px-4 bg-gradient-to-r from-fuchsia-500/85 via-pink-500/85 to-rose-500/85 hover:from-fuchsia-500 hover:via-pink-500 hover:to-rose-500 text-white font-semibold rounded-xl shadow-lg shadow-pink-500/20 hover:shadow-pink-500/40 transition-all transform hover:-translate-y-0.5 active:translate-y-0"
            >
                Login
            </button>
        </form>

        <p class="text-center text-xs text-slate-400 mt-8">
            &copy; {{ date('Y') }} SSB Education. All rights reserved.
        </p>
    </div>
</div>
@endsection
