@extends('layouts.app')

@section('title', 'Login - SSB Education')

@section('content')
<div class="h-screen w-full overflow-hidden flex">

    {{-- LEFT PANEL : plain white, just the big logo --}}
    <div class="hidden lg:flex w-1/2 items-center justify-center px-12 bg-white">
        <img src="{{ $logoUrl }}" alt="SSB Education" class="max-w-[420px] w-full h-auto object-contain drop-shadow-md">
    </div>

    {{-- RIGHT PANEL : soft pink/lavender gradient with form --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-8 bg-gradient-to-br from-pink-100/50 via-rose-50/40 to-purple-100/40">
        <div class="w-full max-w-md">
            <div class="text-center mb-6">
                <img src="{{ $logoUrl }}" alt="SSB Education" class="w-20 h-20 mx-auto mb-2 drop-shadow object-contain lg:hidden">
                <div class="text-[11px] font-semibold tracking-[0.3em] text-pink-500/80">SSB EDUCATION</div>
            </div>

            <h1 class="text-4xl font-extrabold text-slate-800 text-center mb-2">Welcome Back!</h1>
            <p class="text-slate-500 text-center mb-6">Login to continue to your dashboard</p>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="mobile" class="block text-sm font-semibold text-slate-700 mb-1.5">Mobile Number</label>
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
                        class="w-full px-4 py-3 bg-white/80 border border-white/80 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 transition outline-none text-slate-800 placeholder-slate-400 shadow-sm"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            placeholder="Enter your password"
                            class="w-full px-4 pr-12 py-3 bg-white/80 border border-white/80 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 transition outline-none text-slate-800 placeholder-slate-400 shadow-sm"
                        >
                        <button type="button" onclick="const p=document.getElementById('password');p.type=p.type==='password'?'text':'password'" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-pink-500/80">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.46 12C3.73 7.94 7.52 5 12 5c4.48 0 8.27 2.94 9.54 7-1.27 4.06-5.06 7-9.54 7-4.48 0-8.27-2.94-9.54-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="flex items-start gap-2 text-sm text-rose-600">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

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
</div>
@endsection
