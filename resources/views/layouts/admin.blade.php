@extends('layouts.app')

@section('content')
@php
    $isAdmin = auth()->check() && auth()->user()->isAdmin();

    $nav = $isAdmin ? [
        ['label' => 'Dashboard',     'href' => route('dashboard'),       'route' => 'dashboard',  'icon' => 'grid'],
        ['label' => 'Master Data',   'href' => route('master.index'),    'route' => 'master.*',   'icon' => 'database'],
        ['label' => 'Users',         'href' => route('users.index'),     'route' => 'users.*',    'icon' => 'users'],
        ['label' => 'Students',      'href' => route('students.index'),  'route' => 'students.*', 'icon' => 'graduation'],
        ['label' => 'Announcements', 'href' => route('announcements.index'), 'route' => 'announcements.*', 'icon' => 'megaphone'],
        ['label' => 'Pay Fee',       'href' => route('pay-fee.index'),   'route' => 'pay-fee.*',  'icon' => 'cards'],
        ['label' => 'Wallet',        'href' => route('wallet.index'),    'route' => 'wallet.*',   'icon' => 'wallet'],
        ['label' => 'Support',       'href' => route('support.index'),    'route' => 'support.*',  'icon' => 'support'],
        ['label' => 'Enquiries',     'href' => route('enquiries.index'),  'route' => 'enquiries.*', 'icon' => 'enquiries'],
        ['label' => 'Fee Calculator','href' => route('fee-calculator'),  'route' => 'fee-calculator', 'icon' => 'calculator'],
        ['label' => 'Profile',       'href' => route('profile.index'),   'route' => 'profile.*',  'icon' => 'user'],
    ] : [
        ['label' => 'Dashboard',     'href' => route('dashboard'),       'route' => 'dashboard',  'icon' => 'grid'],
        ['label' => 'Master Data',   'href' => route('master.index'),    'route' => 'master.*',   'icon' => 'database'],
        ['label' => 'Students',      'href' => route('students.index'),  'route' => 'students.*', 'icon' => 'graduation'],
        ['label' => 'Announcements', 'href' => route('announcements.index'), 'route' => 'announcements.*', 'icon' => 'megaphone'],
        ['label' => 'Pay Fee',       'href' => route('pay-fee.index'),   'route' => 'pay-fee.*',  'icon' => 'cards'],
        ['label' => 'Wallet',        'href' => route('wallet.index'),    'route' => 'wallet.*',   'icon' => 'wallet'],
        ['label' => 'Support',       'href' => route('support.index'),    'route' => 'support.*',  'icon' => 'support'],
        ['label' => 'Fee Calculator','href' => route('fee-calculator'),  'route' => 'fee-calculator', 'icon' => 'calculator'],
    ];

    $icons = [
        'grid'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>',
        'database'   => '<ellipse cx="12" cy="6" rx="8" ry="3" stroke-linecap="round" stroke-linejoin="round"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 6v6c0 1.66 3.58 3 8 3s8-1.34 8-3V6M4 12v6c0 1.66 3.58 3 8 3s8-1.34 8-3v-6"/>',
        'users'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6 5.87v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M16 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
        'graduation' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>',
        'megaphone'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>',
        'cards'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/>',
        'wallet'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12V7H5a2 2 0 010-4h14v4M3 5v14a2 2 0 002 2h16v-5M18 12a2 2 0 100 4h3v-4h-3z"/>',
        'support'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>',
        'enquiries'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.84L3 20l1.13-3.39A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
        'calculator' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m-6 4h.01M12 11h.01M15 11h.01M9 15h.01M12 15h.01M15 15h.01M5 5a2 2 0 012-2h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"/>',
        'user'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
        'logout'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>',
        'arrowLeft'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>',
        'search'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>',
        'bell'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',
    ];

    // Live wallet balance for the topbar pill — falls back to 0 if the
    // wallet_transactions table doesn't exist yet (e.g. before migrate).
    try {
        $walletAmount = auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('wallet_transactions')
            ? (float) \App\Models\WalletTransaction::where('user_id', auth()->id())->sum('amount')
            : 0;
    } catch (\Throwable $e) {
        $walletAmount = 0;
    }

    // Recent Activity feed for the topbar panel.
    //  • Admin sees every actor's activity (own + every sub-admin's).
    //  • Sub-admin sees their own activity + the admin's announcement /
    //    support / wallet entries (so they get notified when the admin
    //    posts to them, replies, or credits their wallet).
    $recentActivities = collect();
    try {
        if (auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
            $authUser = auth()->user();

            // Per-user soft-hide pivot — entries the user dismissed from
            // their own bell stay hidden across refreshes.
            $hiddenIds = \Illuminate\Support\Facades\Schema::hasTable('activity_log_hides')
                ? \Illuminate\Support\Facades\DB::table('activity_log_hides')
                    ->where('user_id', $authUser->id)
                    ->pluck('activity_log_id')
                    ->all()
                : [];

            $feed = \App\Models\ActivityLog::with('user:id,name,role,avatar_path')
                ->when(! empty($hiddenIds), fn ($q) => $q->whereNotIn('id', $hiddenIds))
                ->orderByDesc('id')
                ->limit(80);

            if (! $isAdmin) {
                // Sub-admin sees:
                //  • their own activity
                //  • admin announcements / support replies / wallet credits/debits
                //  • admin's decisions on payment requests *belonging to this
                //    sub-admin* (scoped via PaymentRequest.user_id)
                $myRequestIds = \Illuminate\Support\Facades\Schema::hasTable('payment_requests')
                    ? \App\Models\PaymentRequest::where('user_id', $authUser->id)->pluck('id')->all()
                    : [];

                $feed->where(function ($q) use ($authUser, $myRequestIds) {
                    $q->where('user_id', $authUser->id)
                      ->orWhere(function ($q2) {
                          $q2->whereHas('user', fn ($u) => $u->where('role', \App\Models\User::ROLE_ADMIN))
                             ->whereIn('action', [
                                 'announcement.created',
                                 'announcement.updated',
                                 'announcement.deleted',
                                 'support.replied',
                                 'wallet.credited',
                                 'wallet.debited',
                             ]);
                      })
                      ->orWhere(function ($q2) use ($myRequestIds) {
                          $q2->whereIn('action', ['wallet.request_approved', 'wallet.request_rejected'])
                             ->where('subject_type', \App\Models\PaymentRequest::class)
                             ->whereIn('subject_id', $myRequestIds);
                      });
                });
            }

            $recentActivities = $feed->get();
        }
    } catch (\Throwable $e) {
        $recentActivities = collect();
    }

    // Bell badge counts only entries newer than the user's last-seen
    // watermark, so an opened-and-closed panel quiets the bell until
    // something fresh appears.
    $lastSeenActivityId = (int) (auth()->user()?->last_seen_activity_id ?? 0);
    $unreadActivityCount = $recentActivities->filter(fn ($a) => (int) $a->id > $lastSeenActivityId)->count();
    $topActivityId = (int) ($recentActivities->max('id') ?? 0);

    $activityCategoryStyles = [
        'announcement' => ['bg' => 'bg-pink-50',    'text' => 'text-pink-600',    'label' => 'Announcement'],
        'support'      => ['bg' => 'bg-amber-50',   'text' => 'text-amber-600',   'label' => 'Support'],
        'wallet'       => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'label' => 'Wallet'],
        'user'         => ['bg' => 'bg-indigo-50',  'text' => 'text-indigo-600',  'label' => 'User'],
        'other'        => ['bg' => 'bg-slate-50',   'text' => 'text-slate-600',   'label' => 'Activity'],
    ];
@endphp

@php
    // Bottom-nav picks the 4 most-used routes per role; the rest live in
    // the "More" sheet (which is just the full nav opened from anywhere).
    $bottomNavRoutes = $isAdmin
        ? ['dashboard', 'users.*', 'students.*', 'pay-fee.*']
        : ['dashboard', 'students.*', 'pay-fee.*', 'wallet.*'];
    $bottomNavItems = collect($bottomNavRoutes)
        ->map(fn ($route) => collect($nav)->firstWhere('route', $route))
        ->filter()
        ->values();
@endphp

<div class="h-screen overflow-hidden flex bg-gradient-to-br from-slate-50 via-pink-50/40 to-slate-50">

    {{-- SIDEBAR (desktop persistent / mobile drawer)
         Same markup serves both — `md:flex md:w-64 md:relative md:translate-x-0` on
         desktop, `fixed inset-y-0 left-0 translate-x-full` on mobile until the
         hamburger toggles it. The backdrop sibling only renders on small screens. --}}
    <div id="mobileSidebarBackdrop"
         class="md:hidden fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-200"
         onclick="closeMobileSidebar()"></div>

    <aside id="appSidebar"
           class="fixed md:relative inset-y-0 left-0 z-50 w-72 md:w-64 bg-white border-r border-slate-200 flex flex-col
                  -translate-x-full md:translate-x-0 transition-transform duration-300 ease-out
                  pl-safe pt-safe">
        <div class="px-6 py-6 border-b border-slate-100 flex flex-col items-center text-center relative">
            @if ($isAdmin)
                <img src="{{ $logoDataUri }}" alt="SSB Education"
                     width="80" height="80" decoding="sync" fetchpriority="high"
                     class="w-20 h-20 object-contain drop-shadow mb-2">
                <div class="font-extrabold text-slate-800 leading-tight text-base">SSB EDUCATION</div>
            @else
                <img src="{{ $loginLeftDataUri }}" alt="Mangalayatan University"
                     width="602" height="414" decoding="sync" fetchpriority="high"
                     class="max-w-full h-auto max-h-28 object-contain drop-shadow">
            @endif

            {{-- Close handle, only visible inside the mobile drawer --}}
            <button type="button" onclick="closeMobileSidebar()" aria-label="Close menu"
                    class="md:hidden absolute top-3 right-3 w-9 h-9 rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700 inline-flex items-center justify-center transition tap">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="px-6 pt-5 pb-2 text-[11px] font-semibold tracking-[0.2em] text-slate-400 uppercase">
            Dashboard
        </div>

        <nav class="flex-1 px-3 pb-4 space-y-1 overflow-y-auto ios-scroll">
            @foreach ($nav as $item)
                @php $active = $item['route'] && request()->routeIs($item['route']); @endphp
                <a href="{{ $item['href'] }}"
                   class="flex items-center gap-3 px-3 py-3 md:py-2.5 rounded-xl text-sm font-medium transition
                          {{ $active
                                ? 'bg-pink-50 text-pink-600 font-semibold ring-1 ring-pink-100'
                                : 'text-slate-600 hover:bg-pink-50/60 hover:text-pink-600 active:bg-pink-50' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        {!! $icons[$item['icon']] !!}
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach

            <button type="button" onclick="openLogoutModal()" class="w-full flex items-center gap-3 px-3 py-3 md:py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-rose-50 hover:text-rose-600 active:bg-rose-50 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    {!! $icons['logout'] !!}
                </svg>
                Logout
            </button>
        </nav>
    </aside>

    {{-- MAIN --}}
    <main class="flex-1 flex flex-col overflow-hidden">

        {{-- TOPBAR
             Mobile: hamburger + tight wallet chip + bell + profile (no
             Back, no search box, no logout — those live in the drawer
             and bottom-nav More sheet).
             Desktop: full topbar with Back, greeting, search, all icons. --}}
        <header class="bg-white border-b border-slate-200 px-3 sm:px-4 lg:px-6 py-2.5 lg:py-3
                       flex items-center gap-2 lg:gap-4 relative z-40 pt-safe pl-safe pr-safe">

            {{-- Hamburger (mobile only) --}}
            <button type="button" onclick="openMobileSidebar()" aria-label="Open menu"
                    class="md:hidden tap w-10 h-10 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 inline-flex items-center justify-center transition shadow-sm">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            {{-- Back button (desktop only — mobile uses the OS back gesture
                 / browser arrow, and the hamburger covers menu) --}}
            <button type="button" onclick="history.back()"
                    class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 text-sm font-medium transition shadow-sm shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    {!! $icons['arrowLeft'] !!}
                </svg>
                Back
            </button>

            {{-- Mobile-only title — replaces the greeting on small screens,
                 keeps the topbar identifiable without a logo. --}}
            <div class="md:hidden flex-1 min-w-0">
                <div class="text-sm font-bold text-slate-800 truncate">
                    {{ $isAdmin ? 'SSB Admin' : 'Hi, '.\Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }}
                </div>
                <div class="text-[10px] text-pink-600 font-semibold">2026–27</div>
            </div>

            {{-- Desktop greeting --}}
            <div class="hidden md:flex items-center gap-2 shrink-0">
                <span class="text-base text-slate-700">
                    Welcome! {{ $isAdmin ? 'SSB EDUCATION ADMIN' : auth()->user()->name }}
                </span>
                <span class="text-xs font-semibold text-pink-600 bg-pink-50 border border-pink-100 px-2 py-0.5 rounded-md">2026–27</span>
            </div>

            {{-- Desktop search --}}
            <div class="hidden md:block flex-1 max-w-xl mx-auto">
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

            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                {{-- Wallet chip — full pill on sm+, compact icon-only on xs --}}
                <div class="hidden xs:flex sm:flex items-center gap-1 sm:gap-1.5 px-2 sm:px-3 h-9 sm:h-auto sm:py-1.5 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs sm:text-sm font-semibold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        {!! $icons['wallet'] !!}
                    </svg>
                    <span class="whitespace-nowrap">₹{{ number_format($walletAmount) }}</span>
                </div>

                <button type="button" title="Recent Activity" onclick="openActivityPanel()"
                        class="relative tap sm:tap-auto w-10 h-10 rounded-full bg-amber-50 border border-amber-100 text-amber-600 hover:bg-amber-100 hover:text-amber-700 flex items-center justify-center transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        {!! $icons['bell'] !!}
                    </svg>
                    <span id="activityBadge"
                          class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center ring-2 ring-white
                                 {{ $unreadActivityCount > 0 ? '' : 'hidden' }}">
                        {{ $unreadActivityCount > 99 ? '99+' : $unreadActivityCount }}
                    </span>
                </button>

                {{-- Profile + Logout are desktop-only; mobile uses the
                     drawer + bottom-nav for these. --}}
                <a href="{{ $isAdmin ? route('profile.index') : route('account.index') }}" title="Profile"
                   class="hidden sm:flex w-10 h-10 rounded-full bg-pink-50 border border-pink-100 text-pink-600 hover:bg-pink-100 hover:text-pink-700 items-center justify-center transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        {!! $icons['user'] !!}
                    </svg>
                </a>

                <button type="button" onclick="openLogoutModal()" title="Logout"
                        class="hidden sm:flex w-10 h-10 rounded-full bg-rose-50 border border-rose-100 text-rose-600 hover:bg-rose-100 hover:text-rose-700 items-center justify-center transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        {!! $icons['logout'] !!}
                    </svg>
                </button>
            </div>
        </header>

        {{-- PAGE BODY + SLIDE-IN PANEL OVERLAY --}}
        {{-- The relative wrapper IS the slide-in panel's positioning context.
             Because it's a flex sibling of the topbar (not fixed to the viewport),
             a panel using `absolute inset-0` lands flush against the topbar's
             bottom edge — no JS measurement, no gap, regardless of topbar height. --}}
        <div class="flex-1 relative overflow-hidden">
            <div class="absolute inset-0 overflow-y-auto ios-scroll">
                @yield('admin-header')
                {{-- pb-28 on mobile so the floating bottom nav doesn't
                     cover the last row of content. Restores normal
                     padding from md+. --}}
                <div class="p-4 sm:p-6 lg:p-10 space-y-6 sm:space-y-8 pb-28 md:pb-10 pl-safe pr-safe">
                    @yield('admin')
                </div>
            </div>
            @yield('slide-panel')

            {{-- RECENT ACTIVITY SLIDE-IN PANEL --}}
            <aside id="activityPanel" class="absolute inset-0 z-40 hidden" aria-hidden="true">
                <div id="activityBackdrop" class="absolute inset-0 bg-slate-900/30 opacity-0 transition-opacity duration-200" onclick="closeActivityPanel()"></div>
                <div id="activityCard"
                     class="absolute right-0 top-0 bottom-0 w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-out">

                    <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                {!! $icons['bell'] !!}
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-slate-800">Recent Activity</h3>
                            <p class="text-[11px] text-slate-500">
                                {{ $isAdmin
                                    ? 'Your activity and every sub-admin\'s activity'
                                    : 'Your activity and admin updates to you' }}
                            </p>
                        </div>
                        <button type="button" onclick="closeActivityPanel()" aria-label="Close"
                                class="w-8 h-8 rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700 inline-flex items-center justify-center transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    @if ($recentActivities->isNotEmpty())
                        <div class="px-5 py-2 border-b border-slate-100 flex items-center gap-3 bg-slate-50/60">
                            <label class="inline-flex items-center gap-2 text-[11px] font-semibold text-slate-600 cursor-pointer select-none">
                                <input id="activitySelectAll" type="checkbox"
                                       class="w-3.5 h-3.5 rounded border-slate-300 text-pink-600 focus:ring-pink-500"
                                       onchange="toggleAllActivities(this.checked)">
                                <span>Select all</span>
                            </label>
                            <span id="activitySelectedCount" class="text-[11px] text-slate-400">0 selected</span>
                            <button type="button" id="activityDeleteBtn"
                                    onclick="deleteSelectedActivities()"
                                    disabled
                                    class="ml-auto inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-[11px] font-semibold text-white bg-rose-500 hover:bg-rose-600 disabled:bg-slate-200 disabled:text-slate-400 disabled:cursor-not-allowed transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                Delete
                            </button>
                        </div>
                    @endif

                    <div class="flex-1 overflow-y-auto">
                        @if ($recentActivities->isEmpty())
                            <div class="px-6 py-16 text-center text-sm text-slate-500">
                                No activity yet.
                            </div>
                        @else
                            <ul id="activityList" class="divide-y divide-slate-100">
                                @foreach ($recentActivities as $log)
                                    @php
                                        $cat = $activityCategoryStyles[$log->category] ?? $activityCategoryStyles['other'];
                                        $actor = $log->user;
                                        $when  = $log->created_at?->setTimezone(config('app.timezone'));
                                    @endphp
                                    <li data-activity-id="{{ $log->id }}"
                                        class="activity-item px-5 py-3 flex items-start gap-3 hover:bg-slate-50/70 transition">
                                        <input type="checkbox" value="{{ $log->id }}"
                                               class="activity-checkbox mt-1.5 w-3.5 h-3.5 rounded border-slate-300 text-pink-600 focus:ring-pink-500 cursor-pointer"
                                               onchange="onActivityCheckChange()">
                                        <div class="w-9 h-9 rounded-lg {{ $cat['bg'] }} {{ $cat['text'] }} flex items-center justify-center shrink-0 mt-0.5 text-[11px] font-bold uppercase">
                                            {{ \Illuminate\Support\Str::of($actor?->name ?? '?')->substr(0, 1) }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="text-sm font-semibold text-slate-800 truncate">{{ $actor?->name ?? 'Unknown' }}</span>
                                                @if ($actor && $actor->role === \App\Models\User::ROLE_ADMIN)
                                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-pink-50 text-pink-700">Admin</span>
                                                @endif
                                                <span class="text-[10px] font-medium px-1.5 py-0.5 rounded {{ $cat['bg'] }} {{ $cat['text'] }}">{{ $cat['label'] }}</span>
                                            </div>
                                            <p class="text-xs text-slate-600 mt-0.5 leading-snug">{{ $log->summary }}</p>
                                            <p class="text-[11px] text-slate-400 mt-1">
                                                {{ $when?->diffForHumans() }}
                                                <span class="text-slate-300">·</span>
                                                {{ $when?->format('d M, h:i A') }} IST
                                            </p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <div id="activityEmpty" class="hidden px-6 py-16 text-center text-sm text-slate-500">
                                No activity yet.
                            </div>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </main>
</div>

{{-- MOBILE BOTTOM NAV — app-style sticky bar with the role's most-used
     routes + a "More" tab that opens a sheet listing the rest. Hidden
     on md+ where the sidebar handles navigation. --}}
<nav id="mobileBottomNav"
     class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur border-t border-slate-200 pb-safe pl-safe pr-safe">
    <div class="grid grid-cols-5 gap-0.5 px-1 pt-1.5">
        @foreach ($bottomNavItems as $item)
            @php $active = $item['route'] && request()->routeIs($item['route']); @endphp
            <a href="{{ $item['href'] }}"
               class="flex flex-col items-center justify-center gap-0.5 py-1.5 rounded-xl transition
                      {{ $active ? 'text-pink-600' : 'text-slate-500 active:bg-slate-50' }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    {!! $icons[$item['icon']] !!}
                </svg>
                <span class="text-[10px] font-semibold leading-none">{{ $item['label'] }}</span>
                @if ($active)
                    <span class="w-1 h-1 rounded-full bg-pink-500"></span>
                @endif
            </a>
        @endforeach
        <button type="button" onclick="openMobileSidebar()"
                class="flex flex-col items-center justify-center gap-0.5 py-1.5 rounded-xl text-slate-500 active:bg-slate-50 transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
            <span class="text-[10px] font-semibold leading-none">More</span>
        </button>
    </div>
</nav>

{{-- BOTTOM STATUS TOAST — pushed above the mobile bottom-nav so it
     never sits behind it. --}}
@if (session('status'))
    <div id="statusToast"
         class="fixed bottom-24 md:bottom-6 left-1/2 -translate-x-1/2 z-[60] px-4 py-2.5 rounded-lg bg-slate-900 text-white text-sm font-medium shadow-lg flex items-center gap-2 transition-all duration-300 max-w-[90vw]">
        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <span>{{ session('status') }}</span>
    </div>
    <script>
        setTimeout(() => {
            const t = document.getElementById('statusToast');
            if (!t) return;
            t.style.opacity = '0';
            t.style.transform = 'translate(-50%, 20px)';
            setTimeout(() => t.remove(), 350);
        }, 3000);
    </script>
@endif

{{-- CONFIRMATION MODAL — themed per call via confirmAction(form, message, title, opts).
     Defaults to a destructive (rose) "Delete" tone; callers can pass
     opts.tone = 'emerald' | 'amber' | 'pink' | 'rose' and opts.confirmLabel
     to repurpose it for non-destructive flows like Upgrade / Reset. --}}
<div id="confirmModal" class="hidden fixed inset-0 z-[55] items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm" onclick="if(event.target===this)closeConfirmModal()">
    <div class="bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 ring-1 ring-slate-100">
        <div class="flex items-start gap-4 mb-5">
            <div id="confirmIconWrap" class="w-11 h-11 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 ring-1 ring-rose-100">
                <svg id="confirmIconSvg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
            </div>
            <div class="pt-0.5">
                <h3 id="confirmTitle" class="font-bold text-slate-800 text-base">Are you sure?</h3>
                <p id="confirmMessage" class="text-sm text-slate-500 mt-1 leading-snug">This action cannot be undone.</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="closeConfirmModal()"
                    class="flex-1 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition">
                Cancel
            </button>
            <button type="button" id="confirmYes"
                    class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold transition">
                Delete
            </button>
        </div>
    </div>
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

    // Reusable confirmation modal. Default tone is destructive ("rose" / "Delete");
    // pass opts.tone + opts.confirmLabel from the call site to repaint it as
    // an Upgrade / Reset / Save dialog without forking the markup.
    //
    //   confirmAction(form, message, title);                       // delete (default)
    //   confirmAction(form, message, title, { tone: 'emerald',
    //                                          confirmLabel: 'Upgrade' });
    //
    const CONFIRM_TONES = {
        rose:    { wrap: 'bg-rose-50 text-rose-600 ring-rose-100',
                   btn:  'bg-rose-600 hover:bg-rose-700' },
        emerald: { wrap: 'bg-emerald-50 text-emerald-600 ring-emerald-100',
                   btn:  'bg-emerald-600 hover:bg-emerald-700' },
        amber:   { wrap: 'bg-amber-50 text-amber-600 ring-amber-100',
                   btn:  'bg-amber-500 hover:bg-amber-600' },
        pink:    { wrap: 'bg-pink-50 text-pink-600 ring-pink-100',
                   btn:  'bg-pink-600 hover:bg-pink-700' },
        slate:   { wrap: 'bg-slate-50 text-slate-600 ring-slate-100',
                   btn:  'bg-slate-700 hover:bg-slate-800' },
    };

    function openConfirmModal(form, message, title, opts) {
        opts = opts || {};
        const tone = CONFIRM_TONES[opts.tone] || CONFIRM_TONES.rose;
        const label = opts.confirmLabel || 'Delete';

        document.getElementById('confirmTitle').textContent   = title   || 'Are you sure?';
        document.getElementById('confirmMessage').textContent = message || 'This action cannot be undone.';

        const iconWrap = document.getElementById('confirmIconWrap');
        if (iconWrap) {
            iconWrap.className = 'w-11 h-11 rounded-full flex items-center justify-center shrink-0 ring-1 ' + tone.wrap;
        }

        const yes = document.getElementById('confirmYes');
        yes.className   = 'flex-1 py-2.5 rounded-xl text-white text-sm font-semibold transition ' + tone.btn;
        yes.textContent = label;
        yes.onclick     = () => { closeConfirmModal(); form.submit(); };

        const m = document.getElementById('confirmModal');
        m.classList.remove('hidden');
        m.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    function closeConfirmModal() {
        const m = document.getElementById('confirmModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
        document.body.style.overflow = '';
    }
    function confirmAction(form, message, title, opts) {
        openConfirmModal(form, message, title, opts);
        return false; // block native form submit
    }

    // ───── Mobile sidebar drawer ─────
    function openMobileSidebar() {
        const aside = document.getElementById('appSidebar');
        const back  = document.getElementById('mobileSidebarBackdrop');
        if (!aside || !back) return;
        aside.classList.remove('-translate-x-full');
        back.classList.remove('opacity-0', 'pointer-events-none');
        back.classList.add('opacity-100');
        document.body.style.overflow = 'hidden';
    }
    function closeMobileSidebar() {
        const aside = document.getElementById('appSidebar');
        const back  = document.getElementById('mobileSidebarBackdrop');
        if (!aside || !back) return;
        aside.classList.add('-translate-x-full');
        back.classList.add('opacity-0', 'pointer-events-none');
        back.classList.remove('opacity-100');
        document.body.style.overflow = '';
    }
    // The drawer pinning `-translate-x-full` at all viewport sizes plays
    // poorly when you resize from mobile to desktop. Recompute on resize
    // so md+ always wins.
    window.addEventListener('resize', () => {
        const aside = document.getElementById('appSidebar');
        if (!aside) return;
        if (window.matchMedia('(min-width: 768px)').matches) {
            aside.classList.remove('-translate-x-full');
            document.body.style.overflow = '';
            document.getElementById('mobileSidebarBackdrop')
                ?.classList.add('opacity-0', 'pointer-events-none');
        } else {
            aside.classList.add('-translate-x-full');
        }
    });

    // Cleared the moment the user opens the panel — we never want to
    // mark the same set of activities as seen twice in one page load.
    let activityAlreadyMarkedSeen = false;

    function openActivityPanel() {
        const panel    = document.getElementById('activityPanel');
        const card     = document.getElementById('activityCard');
        const backdrop = document.getElementById('activityBackdrop');
        if (!panel) return;
        panel.classList.remove('hidden');
        panel.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(() => {
            backdrop.classList.add('opacity-100');
            backdrop.classList.remove('opacity-0');
            card.classList.remove('translate-x-full');
        });
        markActivityFeedSeen();
    }

    function markActivityFeedSeen() {
        if (activityAlreadyMarkedSeen) return;
        const badge = document.getElementById('activityBadge');
        // Optimistically hide the badge — the server call below persists
        // the change so a refresh keeps it cleared.
        if (badge) {
            badge.textContent = '0';
            badge.classList.add('hidden');
        }
        const topId = @json($topActivityId);
        if (!topId) {
            activityAlreadyMarkedSeen = true;
            return;
        }
        const url  = @json(route('activities.seen'));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ last_id: topId }),
        }).then(() => {
            activityAlreadyMarkedSeen = true;
        }).catch(() => {
            // Network blip — leave the flag false so the next open retries.
        });
    }
    function closeActivityPanel() {
        const panel    = document.getElementById('activityPanel');
        const card     = document.getElementById('activityCard');
        const backdrop = document.getElementById('activityBackdrop');
        if (!panel) return;
        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
        card.classList.add('translate-x-full');
        setTimeout(() => {
            panel.classList.add('hidden');
            panel.setAttribute('aria-hidden', 'true');
        }, 250);
    }

    function toggleAllActivities(checked) {
        document.querySelectorAll('.activity-checkbox').forEach(cb => { cb.checked = checked; });
        onActivityCheckChange();
    }

    function onActivityCheckChange() {
        const boxes = Array.from(document.querySelectorAll('.activity-checkbox'));
        const visible = boxes.filter(cb => cb.closest('.activity-item') && !cb.closest('.activity-item').classList.contains('hidden'));
        const selected = visible.filter(cb => cb.checked);
        const countEl = document.getElementById('activitySelectedCount');
        const btn = document.getElementById('activityDeleteBtn');
        const all = document.getElementById('activitySelectAll');
        if (countEl) countEl.textContent = selected.length + ' selected';
        if (btn) btn.disabled = selected.length === 0;
        if (all) {
            all.checked = visible.length > 0 && selected.length === visible.length;
            all.indeterminate = selected.length > 0 && selected.length < visible.length;
        }
    }

    function deleteSelectedActivities() {
        const boxes = Array.from(document.querySelectorAll('.activity-checkbox'))
            .filter(cb => cb.checked && cb.closest('.activity-item') && !cb.closest('.activity-item').classList.contains('hidden'));
        const ids = boxes.map(cb => parseInt(cb.value, 10)).filter(Boolean);
        if (!ids.length) return;
        const btn = document.getElementById('activityDeleteBtn');
        if (btn) btn.disabled = true;
        const url  = @json(route('activities.destroy'));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ ids: ids }),
        }).then(r => r.json()).then(res => {
            if (res && res.ok) {
                ids.forEach(id => {
                    const li = document.querySelector('.activity-item[data-activity-id="'+id+'"]');
                    if (li) li.remove();
                });
                const remaining = document.querySelectorAll('.activity-item').length;
                if (remaining === 0) {
                    const list  = document.getElementById('activityList');
                    const empty = document.getElementById('activityEmpty');
                    if (list)  list.classList.add('hidden');
                    if (empty) empty.classList.remove('hidden');
                }
                // Decrement the topbar badge by the count we just cleared.
                const badge = document.getElementById('activityBadge');
                if (badge && !badge.classList.contains('hidden')) {
                    const current = parseInt(badge.textContent, 10) || 0;
                    const next = Math.max(0, current - ids.length);
                    if (next === 0) {
                        badge.classList.add('hidden');
                    } else {
                        badge.textContent = String(next);
                    }
                }
                onActivityCheckChange();
            } else if (btn) {
                btn.disabled = false;
            }
        }).catch(() => {
            if (btn) btn.disabled = false;
        });
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeMobileSidebar();
            closeLogoutModal();
            closeConfirmModal();
            closeActivityPanel();
        }
    });

    // Slide-in panels now position themselves via CSS layout (absolute inset-0
    // inside a flex sibling of the topbar), so no JS topbar measurement is
    // needed — the panel always lands flush against the topbar's bottom edge.
</script>
@endsection
