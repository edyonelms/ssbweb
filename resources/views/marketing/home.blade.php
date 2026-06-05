@php
    /** @var \Illuminate\Support\Collection $universities */
    /** @var \Illuminate\Support\Collection $courses */
    /** @var string $loginUrl */

    $logo = \Cache::rememberForever('asset:logo', fn () =>
        is_file(public_path('images/logo.png'))
            ? 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('images/logo.png')))
            : asset('images/logo.png')
    );
    $hero = \Cache::rememberForever('asset:login-left', fn () =>
        is_file(public_path('images/login-left.png'))
            ? 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('images/login-left.png')))
            : asset('images/login-left.png')
    );

    $founders = [
        [
            'name'  => 'Founder & CEO',
            'role'  => 'Visionary · Education Strategist',
            'bio'   => 'A career educator who set up SSB Education with one promise — make a recognised online degree as straightforward and affordable as walking into a campus. Has spent over a decade designing learning programs for first-generation students across Tier-2 and Tier-3 India, and personally still answers admission queries every Saturday morning.',
            'initial' => 'S',
            'tint'  => 'from-pink-100 to-rose-100 text-pink-600',
        ],
        [
            'name'  => 'Co-Founder & Director',
            'role'  => 'Academic Partnerships · Operations',
            'bio'   => 'Holds the relationships with Mangalayatan University and the other UGC-recognised institutions whose programs SSB delivers online. Owns curriculum alignment, exam logistics and the day-to-day operations that keep cohorts moving — from admission paperwork to convocation. Background in academic administration with on-campus experience.',
            'initial' => 'B',
            'tint'  => 'from-violet-100 to-fuchsia-100 text-violet-600',
        ],
        [
            'name'  => 'Co-Founder & Head of Mentorship',
            'role'  => 'Student Success · Career Guidance',
            'bio'   => 'Leads the mentor network that handles every student touchpoint after admission — weekly study check-ins, exam prep, internship leads and post-graduation career support. Counsels students 1:1 on choosing the right program for the life they\'re trying to build, not the one a brochure tells them to want.',
            'initial' => 'B',
            'tint'  => 'from-emerald-100 to-teal-100 text-emerald-600',
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SSB Education — Earn UGC-recognised online degrees from Mangalayatan University. Affordable, flexible programs designed for working students.">
    <title>SSB Education · Online degrees in partnership with Mangalayatan University</title>

    <link rel="icon" type="image/png" href="{{ $logo }}">
    <link rel="shortcut icon" type="image/png" href="{{ $logo }}">
    <link rel="apple-touch-icon" href="{{ $logo }}">

    <link rel="preconnect" href="https://cdn.tailwindcss.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .text-gradient { background: linear-gradient(90deg, #d946ef, #ec4899, #f43f5e); -webkit-background-clip: text; background-clip: text; color: transparent; }
    </style>
</head>
<body class="bg-white text-slate-800 antialiased">

{{-- ─── NAVBAR ─── --}}
<header class="fixed top-0 inset-x-0 z-40 bg-white/85 backdrop-blur border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        <a href="#top" class="flex items-center gap-3">
            <img src="{{ $logo }}" alt="SSB Education" class="w-14 h-14 object-contain">
            <div class="leading-tight">
                <p class="text-base font-extrabold text-slate-800">SSB Education</p>
                <p class="text-[11px] text-slate-500 -mt-0.5">in partnership with Mangalayatan</p>
            </div>
        </a>

        <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-slate-600">
            <a href="#about"      class="hover:text-pink-600 transition">About</a>
            <a href="#universities" class="hover:text-pink-600 transition">Universities</a>
            <a href="#courses"    class="hover:text-pink-600 transition">Courses</a>
            <a href="#founders"   class="hover:text-pink-600 transition">Founders</a>
            <a href="#contact"    class="hover:text-pink-600 transition">Contact</a>
        </nav>

        <a href="{{ $loginUrl }}"
           class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-semibold bg-gradient-to-r from-fuchsia-500 to-pink-500 text-white hover:from-fuchsia-600 hover:to-pink-600 shadow-md shadow-pink-500/20 transition">
            Login
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    </div>
</header>

<main id="top" class="pt-20">

    {{-- ─── HERO ─── --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-pink-50 via-white to-violet-50"></div>
        <div class="absolute -top-20 -right-20 w-80 h-80 rounded-full bg-pink-200/40 blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 rounded-full bg-violet-200/40 blur-3xl"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-pink-50 border border-pink-100 text-xs font-semibold text-pink-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-pink-500 animate-pulse"></span>
                    UGC-recognised online degrees
                </span>
                <h1 class="mt-5 text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight tracking-tight text-slate-900">
                    Your degree.<br>
                    <span class="text-gradient">On your terms.</span>
                </h1>
                <p class="mt-5 text-base sm:text-lg text-slate-600 max-w-xl leading-relaxed">
                    SSB Education partners with <span class="font-semibold text-slate-800">Mangalayatan University</span> to bring affordable, flexible online programs to students who can't put their life on hold to attend campus.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="#contact"
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-gradient-to-r from-fuchsia-500 to-pink-500 hover:from-fuchsia-600 hover:to-pink-600 text-white text-sm font-semibold shadow-lg shadow-pink-500/30 transition">
                        Talk to a Counsellor
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="#courses"
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-full border border-slate-200 hover:border-slate-300 text-slate-700 text-sm font-semibold transition">
                        Browse Courses
                    </a>
                </div>

                <div class="mt-10 grid grid-cols-3 gap-6 max-w-md">
                    <div>
                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-800">{{ max($universities->count(), 1) }}+</p>
                        <p class="text-xs text-slate-500 mt-1">Partner universities</p>
                    </div>
                    <div>
                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-800">100+</p>
                        <p class="text-xs text-slate-500 mt-1">Programs offered</p>
                    </div>
                    <div>
                        <p class="text-2xl sm:text-3xl font-extrabold text-slate-800">100%</p>
                        <p class="text-xs text-slate-500 mt-1">Online · UGC approved</p>
                    </div>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -inset-6 bg-gradient-to-br from-pink-200/60 to-violet-200/60 rounded-3xl blur-2xl"></div>
                <div class="relative bg-white rounded-3xl shadow-2xl p-8 border border-slate-100">
                    <img src="{{ $hero }}" alt="Mangalayatan University" class="w-full max-h-72 object-contain">
                    <div class="mt-6 flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-100 rounded-xl">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <div>
                            <p class="text-sm font-semibold text-emerald-800">UGC-DEB recognised</p>
                            <p class="text-xs text-emerald-700/80">Programs that count, from a campus you trust</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── ABOUT ─── --}}
    <section id="about" class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <span class="inline-block px-3 py-1 rounded-full bg-pink-50 text-xs font-semibold text-pink-700 mb-4">About SSB</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">An education partner built for the real world.</h2>
                <p class="mt-4 text-base sm:text-lg text-slate-600 leading-relaxed">
                    SSB Education is an authorised partner of Mangalayatan University. We make it simple for students — working professionals, family caregivers, anyone juggling life — to enrol in UGC-recognised online degree, diploma and certification programs and finish what they started.
                </p>
            </div>

            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 rounded-2xl border border-slate-100 bg-gradient-to-br from-white to-pink-50/40 hover:shadow-md transition">
                    <div class="w-11 h-11 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-800">Recognised programs</h3>
                    <p class="mt-1.5 text-sm text-slate-600 leading-relaxed">Every program we offer is UGC-DEB approved through our partner university — your degree carries the same weight as a campus one.</p>
                </div>
                <div class="p-6 rounded-2xl border border-slate-100 bg-gradient-to-br from-white to-violet-50/40 hover:shadow-md transition">
                    <div class="w-11 h-11 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-800">Learn at your pace</h3>
                    <p class="mt-1.5 text-sm text-slate-600 leading-relaxed">100% online classes, recorded lectures and a flexible exam calendar so you can study around your day job, family, or own venture.</p>
                </div>
                <div class="p-6 rounded-2xl border border-slate-100 bg-gradient-to-br from-white to-emerald-50/40 hover:shadow-md transition">
                    <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M16 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-800">A mentor at every step</h3>
                    <p class="mt-1.5 text-sm text-slate-600 leading-relaxed">Counselling, admission paperwork, fee planning, exam reminders, career support — our team stays with you from enrolment to graduation.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ─── UNIVERSITIES ─── --}}
    <section id="universities" class="py-20 lg:py-24 bg-slate-50 border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-pink-50 text-xs font-semibold text-pink-700 mb-3">Universities & Boards</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Recognised partners. Real degrees.</h2>
                    <p class="mt-3 text-base text-slate-600 max-w-2xl">We deliver programs in partnership with these UGC-approved universities and boards.</p>
                </div>
            </div>

            @if ($universities->isEmpty())
                <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-500">
                    University details will appear here soon. <a href="#contact" class="text-pink-600 font-semibold hover:underline">Reach out</a> for the latest list.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($universities as $u)
                        <div class="bg-white rounded-2xl p-5 border border-slate-100 hover:border-pink-200 hover:shadow-md transition flex flex-col">
                            <div class="flex items-center gap-3">
                                @if ($u->image_url)
                                    <img src="{{ $u->image_url }}" alt="" class="w-12 h-12 rounded-lg object-cover bg-slate-100">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-pink-100 to-rose-100 text-pink-600 font-bold flex items-center justify-center">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</div>
                                @endif
                                <span class="text-[10px] font-semibold uppercase tracking-wider {{ $u->type === 'board' ? 'text-emerald-600' : 'text-pink-600' }}">{{ ucfirst($u->type) }}</span>
                            </div>
                            <h3 class="mt-4 font-bold text-slate-800 leading-snug">{{ $u->name }}</h3>
                            @if ($u->address)
                                <p class="mt-1 text-xs text-slate-500 line-clamp-2">{{ $u->address }}</p>
                            @endif
                            @if ($u->website)
                                <a href="{{ $u->website }}" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-pink-600 hover:underline">Visit website
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14 3h7v7m0-7L10 14M21 14v7H3V3h7"/></svg>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ─── COURSES ─── --}}
    <section id="courses" class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full bg-pink-50 text-xs font-semibold text-pink-700 mb-3">Programs</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Programs available right now.</h2>
                    <p class="mt-3 text-base text-slate-600 max-w-2xl">A curated list of online programs you can enrol in. Speak to a counsellor for fee structure and admission timeline.</p>
                </div>
            </div>

            @if ($courses->isEmpty())
                <div class="bg-slate-50 rounded-2xl border border-slate-200 p-12 text-center text-slate-500">
                    Course catalogue is being prepared. <a href="#contact" class="text-pink-600 font-semibold hover:underline">Drop us a note</a> and we'll share what's open this season.
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($courses as $c)
                        <div class="bg-white rounded-2xl p-6 border border-slate-100 hover:border-pink-200 hover:shadow-lg transition">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-pink-600 truncate">{{ $c->university?->name ?: 'Partner university' }}</p>
                                    <h3 class="mt-1 font-bold text-slate-800 leading-snug">{{ $c->name }}</h3>
                                </div>
                                @if ($c->lateral_entry)
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 shrink-0">Lateral Entry</span>
                                @endif
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                                <div>
                                    <p class="text-slate-400 uppercase tracking-wider text-[10px] font-semibold">Duration</p>
                                    <p class="mt-0.5 font-semibold text-slate-700">{{ rtrim(rtrim(number_format((float) $c->duration_years, 1), '0'), '.') }} years</p>
                                </div>
                                <div>
                                    <p class="text-slate-400 uppercase tracking-wider text-[10px] font-semibold">Mode</p>
                                    <p class="mt-0.5 font-semibold text-slate-700 capitalize">{{ $c->mode ?: 'Regular' }}</p>
                                </div>
                            </div>
                            @if ($c->subjects)
                                <p class="mt-4 text-xs text-slate-500 line-clamp-2">{{ $c->subjects }}</p>
                            @endif
                            <a href="#contact" class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-pink-600 hover:text-pink-700">
                                Enquire about this program
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- ─── FOUNDERS ─── --}}
    <section id="founders" class="py-20 lg:py-24 bg-slate-50 border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <span class="inline-block px-3 py-1 rounded-full bg-pink-50 text-xs font-semibold text-pink-700 mb-3">Founders</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Built by educators who've been on both sides of the classroom.</h2>
            </div>

            <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($founders as $f)
                    <div class="bg-white rounded-2xl p-6 border border-slate-100 hover:shadow-md transition flex flex-col">
                        <div class="relative w-28 h-28 rounded-full bg-gradient-to-br {{ $f['tint'] }} flex items-center justify-center overflow-hidden ring-4 ring-white shadow-md">
                            <span class="text-4xl font-extrabold">{{ $f['initial'] }}</span>
                            <svg class="absolute bottom-0 w-16 h-16 opacity-30" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/>
                            </svg>
                        </div>
                        <h3 class="mt-5 text-base font-bold text-slate-800">{{ $f['name'] }}</h3>
                        <p class="text-xs font-semibold text-pink-600 mt-0.5">{{ $f['role'] }}</p>
                        <p class="mt-3 text-sm text-slate-600 leading-relaxed">{{ $f['bio'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ─── CONTACT ─── --}}
    <section id="contact" class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-2 gap-12">
            <div>
                <span class="inline-block px-3 py-1 rounded-full bg-pink-50 text-xs font-semibold text-pink-700 mb-3">Contact</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Tell us about you — we'll plan the rest.</h2>
                <p class="mt-4 text-base text-slate-600 max-w-md leading-relaxed">
                    Whether you're picking a program, comparing fees, or already enrolled and need help, we're a message away. Drop your details and our counselling team will reach out within one working day.
                </p>

                <div class="mt-10 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Call us</p>
                            <p class="text-sm font-bold text-slate-800">+91 90123 46006</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Email</p>
                            <p class="text-sm font-bold text-slate-800">hello@ssbeducation.in</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Address</p>
                            <p class="text-sm font-bold text-slate-800">SSB Education,<br>Aligarh, Uttar Pradesh</p>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                @if (session('status'))
                    <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold">
                        ✓ {{ session('status') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm">
                        <p class="font-semibold mb-1">Please fix the following:</p>
                        <ul class="list-disc list-inside text-xs">
                            @foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('marketing.enquiry') }}" class="bg-white rounded-2xl border border-slate-100 shadow-xl p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required maxlength="255" value="{{ old('name') }}" placeholder="Your full name"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Phone</label>
                            <input type="tel" name="phone" maxlength="30" value="{{ old('phone') }}" placeholder="10-digit mobile"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email</label>
                        <input type="email" name="email" maxlength="255" value="{{ old('email') }}" placeholder="you@example.com"
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Course of interest</label>
                        <input type="text" name="subject" maxlength="255" value="{{ old('subject') }}" placeholder="e.g. BBA Online, BCA Distance, MBA..."
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Message <span class="text-rose-500">*</span></label>
                        <textarea name="message" rows="5" maxlength="5000" required placeholder="Tell us what you'd like to know..."
                                  class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">{{ old('message') }}</textarea>
                    </div>
                    <button type="submit"
                            class="w-full py-3 rounded-xl bg-gradient-to-r from-fuchsia-500 to-pink-500 hover:from-fuchsia-600 hover:to-pink-600 text-white text-sm font-semibold shadow-lg shadow-pink-500/20 transition">
                        Send enquiry
                    </button>
                    <p class="text-[11px] text-slate-500 text-center">We respond within one working day · No spam, ever.</p>
                </form>
            </div>
        </div>
    </section>
</main>

{{-- ─── FOOTER (light/cool) ─── --}}
<footer class="bg-gradient-to-br from-slate-50 via-white to-pink-50/50 border-t border-slate-200 text-slate-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div class="md:col-span-2">
                <div class="flex items-center gap-3">
                    <img src="{{ $logo }}" alt="SSB Education" class="w-14 h-14 object-contain">
                    <div class="leading-tight">
                        <p class="text-base font-extrabold text-slate-800">SSB Education</p>
                        <p class="text-[11px] text-slate-500 -mt-0.5">in partnership with Mangalayatan</p>
                    </div>
                </div>
                <p class="mt-5 text-sm text-slate-600 max-w-md leading-relaxed">
                    SSB Education makes UGC-recognised online degree, diploma and certification programs accessible to every learner — whether you're starting out or going back to school.
                </p>
                <div class="mt-6 flex items-center gap-3">
                    {{-- Instagram --}}
                    <a href="https://instagram.com/ssbeducation" target="_blank" rel="noopener"
                       class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:text-pink-600 hover:border-pink-200 hover:shadow-md transition" aria-label="Instagram">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                    {{-- WhatsApp --}}
                    <a href="https://wa.me/919012346006" target="_blank" rel="noopener"
                       class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:text-emerald-600 hover:border-emerald-200 hover:shadow-md transition" aria-label="WhatsApp">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347zM12.05 21.785h-.008c-1.77 0-3.506-.477-5.022-1.378l-.36-.214-3.737.98.998-3.648-.235-.374a9.864 9.864 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.886-9.885 9.886zm8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                    {{-- Email --}}
                    <a href="mailto:hello@ssbeducation.in"
                       class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:text-sky-600 hover:border-sky-200 hover:shadow-md transition" aria-label="Email">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </a>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold text-slate-800 uppercase tracking-wider">Explore</p>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="#about"        class="text-slate-600 hover:text-pink-600 transition">About us</a></li>
                    <li><a href="#universities" class="text-slate-600 hover:text-pink-600 transition">Universities</a></li>
                    <li><a href="#courses"      class="text-slate-600 hover:text-pink-600 transition">Courses</a></li>
                    <li><a href="#founders"     class="text-slate-600 hover:text-pink-600 transition">Founders</a></li>
                    <li><a href="#contact"      class="text-slate-600 hover:text-pink-600 transition">Contact</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <p>© {{ date('Y') }} SSB Education. All rights reserved.</p>
            <p>Made with care for students across India.</p>
        </div>
    </div>
</footer>

</body>
</html>
