@extends('layouts.admin')

@section('title', 'Dashboard - SSB Education')

@php
    /** @var \App\Models\User $user */
    /** @var bool $isAdmin */
    /** @var array $studentCounts */
    /** @var array $studentChart */
    /** @var \Illuminate\Support\Collection $studentsByUniversity */
    /** @var array $feeSummary */
    /** @var array $collectionTrend */
    /** @var float $totalRegistrationFee */
    /** @var float $totalCourseFees */
    /** @var int $totalCourses */
    /** @var array $feeChart */
    /** @var \Illuminate\Support\Collection $feesByUniversity */
    /** @var array $supportStats */
    /** @var array $enquiryStats */
    /** @var \Illuminate\Support\Collection $activities */

    $period = in_array(request('student_period'), ['today','yesterday','7','15','30'], true)
        ? request('student_period')
        : '7';

    $periodLabels = [
        'today'     => 'Today',
        'yesterday' => 'Yesterday',
        '7'         => 'Last 7 days',
        '15'        => 'Last 15 days',
        '30'        => 'Last 30 days',
    ];

    $activityIconMap = [
        'student'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>',
        'announcement' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>',
        'support'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'fee'          => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/>',
        'university'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M14 9h.01M14 13h.01"/>',
        'enquiry'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.84L3 20l1.13-3.39A7.94 7.94 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
    ];

    $activityColorMap = [
        'student'      => 'bg-pink-50 text-pink-600',
        'announcement' => 'bg-amber-50 text-amber-600',
        'support'      => 'bg-sky-50 text-sky-600',
        'fee'          => 'bg-emerald-50 text-emerald-600',
        'university'   => 'bg-violet-50 text-violet-600',
        'enquiry'      => 'bg-rose-50 text-rose-600',
    ];

    $rupees = fn ($v) => '₹'.number_format((float) $v);

    $studentMomentum = $studentCounts['this_month'] - $studentCounts['last_month'];
    $feeMomentum     = $feeSummary['collected_this_month'] - $feeSummary['collected_last_month'];
@endphp

@section('admin-header')
<div class="sticky top-0 z-20 bg-white border-b border-slate-200">
    <div class="px-6 lg:px-10 py-3 flex flex-wrap items-baseline gap-x-4 gap-y-1">
        <h2 class="text-base font-bold text-slate-800">Dashboard</h2>
        <p class="text-xs text-slate-500">Welcome back, <span class="font-semibold text-slate-700">{{ $user->name }}</span> — here's what's happening today</p>
    </div>
</div>
@endsection

@section('admin')

{{-- ─── ROW 1 — STUDENT ANALYTICS (now at the top) ─── --}}
<div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Students Overview</h3>
            <p class="text-[11px] text-slate-500">{{ $isAdmin ? 'Every student across the platform' : 'Students you have added' }}</p>
        </div>
        <a href="{{ route('students.index') }}" class="text-xs font-semibold text-pink-600 hover:underline">View all →</a>
    </div>

    {{-- Headline cards: Total · This Month · Last Month --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
        <div class="rounded-xl border border-pink-100 bg-gradient-to-br from-pink-50 to-rose-50 p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-pink-700">Total Students</p>
            <p class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-800">{{ number_format($studentCounts['total']) }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">All-time registrations on record</p>
        </div>
        <div class="rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50 p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-700">This Month Registered</p>
            <p class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-800">{{ number_format($studentCounts['this_month']) }}</p>
            <p class="text-[10px] mt-0.5
                      {{ $studentMomentum >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ $studentMomentum >= 0 ? '▲' : '▼' }} {{ number_format(abs($studentMomentum)) }} vs last month
            </p>
        </div>
        <div class="rounded-xl border border-violet-100 bg-gradient-to-br from-violet-50 to-fuchsia-50 p-4">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-violet-700">Last Month Registered</p>
            <p class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-800">{{ number_format($studentCounts['last_month']) }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">{{ now()->subMonthNoOverflow()->format('F Y') }}</p>
        </div>
    </div>

    {{-- University breakdown + 30-day chart side by side --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 border-t border-slate-100 pt-5">
        <div class="lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                <p class="text-[11px] text-slate-500 font-semibold">Daily registrations · last 30 days</p>
                <div class="flex items-center gap-1 flex-wrap">
                    @foreach ($periodLabels as $key => $label)
                        @php $isActive = $period === $key; @endphp
                        <a href="{{ route('dashboard', ['student_period' => $key]) }}"
                           class="px-2.5 py-1 rounded-full text-[10px] font-semibold transition
                                  {{ $isActive ? 'bg-pink-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            {{ $label }}
                            <span class="opacity-80">· {{ $studentCounts[$key] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="h-44 sm:h-52">
                <canvas id="studentChart"></canvas>
            </div>
        </div>
        <div>
            <p class="text-[11px] text-slate-500 font-semibold mb-2">By university · top {{ $studentsByUniversity->count() }}</p>
            <ul class="space-y-2">
                @forelse ($studentsByUniversity as $row)
                    @php
                        $share = $studentCounts['total'] > 0 ? round(($row['total'] / max(1, $studentCounts['total'])) * 100) : 0;
                    @endphp
                    <li>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-700 truncate pr-2 max-w-[60%]">{{ $row['university'] }}</span>
                            <span class="text-slate-800 font-bold">{{ number_format($row['total']) }}</span>
                        </div>
                        <div class="mt-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-pink-500 to-rose-400" style="width: {{ $share }}%"></div>
                        </div>
                    </li>
                @empty
                    <li class="text-xs text-slate-400 text-center py-4">No students enrolled yet</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

{{-- ─── ROW 2 — LIVE FEE ANALYTICS SUMMARY ─── --}}
<div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Fee Analytics</h3>
            <p class="text-[11px] text-slate-500">Live picture of what's billable, collected, and pending</p>
        </div>
        <a href="{{ route('pay-fee.index') }}" class="text-xs font-semibold text-pink-600 hover:underline">Collect →</a>
    </div>

    {{-- 5 headline cards — total to collect, collected, remaining, this/last month --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="rounded-xl border border-slate-100 bg-gradient-to-br from-slate-50 to-white p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Total to Collect</p>
            <p class="mt-1 text-lg sm:text-xl font-extrabold text-slate-800">{{ $rupees($feeSummary['total_to_collect']) }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Across {{ number_format($feeSummary['student_count_with_fee']) }} student(s)</p>
        </div>
        <div class="rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-teal-50 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-700">Collected</p>
            <p class="mt-1 text-lg sm:text-xl font-extrabold text-slate-800">{{ $rupees($feeSummary['total_collected']) }}</p>
            @php
                $pct = $feeSummary['total_to_collect'] > 0
                    ? round(($feeSummary['total_collected'] / $feeSummary['total_to_collect']) * 100)
                    : 0;
            @endphp
            <p class="text-[10px] text-emerald-600 mt-0.5">{{ $pct }}% of total billed</p>
        </div>
        <div class="rounded-xl border border-rose-100 bg-gradient-to-br from-rose-50 to-pink-50 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-rose-700">Remaining</p>
            <p class="mt-1 text-lg sm:text-xl font-extrabold text-slate-800">{{ $rupees($feeSummary['total_remaining']) }}</p>
            <p class="text-[10px] text-rose-600 mt-0.5">Outstanding from students</p>
        </div>
        <div class="rounded-xl border border-pink-100 bg-gradient-to-br from-pink-50 to-fuchsia-50 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-pink-700">This Month Collected</p>
            <p class="mt-1 text-lg sm:text-xl font-extrabold text-slate-800">{{ $rupees($feeSummary['collected_this_month']) }}</p>
            <p class="text-[10px] mt-0.5
                      {{ $feeMomentum >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ $feeMomentum >= 0 ? '▲' : '▼' }} {{ $rupees(abs($feeMomentum)) }} vs last month
            </p>
        </div>
        <div class="rounded-xl border border-violet-100 bg-gradient-to-br from-violet-50 to-fuchsia-50 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-violet-700">Last Month Collected</p>
            <p class="mt-1 text-lg sm:text-xl font-extrabold text-slate-800">{{ $rupees($feeSummary['collected_last_month']) }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">{{ now()->subMonthNoOverflow()->format('F Y') }}</p>
        </div>
    </div>

    {{-- Progress bar — how close we are to clearing the billable total --}}
    @if ($feeSummary['total_to_collect'] > 0)
        <div class="mt-4">
            <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                <span>Overall collection progress</span>
                <span class="font-semibold text-slate-700">{{ $pct }}%</span>
            </div>
            <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all" style="width: {{ $pct }}%"></div>
            </div>
        </div>
    @endif
</div>

{{-- ─── ROW 3 — DETAILED FEE COLLECTION (today / this week / by-uni breakdown) ─── --}}
<div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Collection Detail</h3>
            <p class="text-[11px] text-slate-500">{{ $isAdmin ? 'How cash is flowing in across all sub-admins' : 'How cash is flowing in for your students' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="rounded-xl border border-slate-100 bg-emerald-50/40 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-700">Today</p>
            <p class="mt-1 text-lg font-extrabold text-slate-800">{{ $rupees($feeSummary['collected_today']) }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">{{ now()->format('d M Y') }}</p>
        </div>
        <div class="rounded-xl border border-slate-100 bg-sky-50/40 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-sky-700">This Week</p>
            <p class="mt-1 text-lg font-extrabold text-slate-800">{{ $rupees($feeSummary['collected_this_week']) }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Since {{ now()->startOfWeek()->format('d M') }}</p>
        </div>
        <div class="rounded-xl border border-slate-100 bg-pink-50/40 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-pink-700">This Month</p>
            <p class="mt-1 text-lg font-extrabold text-slate-800">{{ $rupees($feeSummary['collected_this_month']) }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">{{ now()->format('F Y') }}</p>
        </div>
        <div class="rounded-xl border border-slate-100 bg-amber-50/40 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-700">Outstanding</p>
            <p class="mt-1 text-lg font-extrabold text-slate-800">{{ $rupees($feeSummary['total_remaining']) }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Yet to be collected</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 border-t border-slate-100 pt-5">
        <div class="lg:col-span-2">
            <p class="text-[11px] text-slate-500 font-semibold mb-2">Daily collection · last 14 days</p>
            <div class="h-44 sm:h-52">
                <canvas id="collectionChart"></canvas>
            </div>
        </div>
        <div>
            <p class="text-[11px] text-slate-500 font-semibold mb-2">Fee structures · by university (top {{ $feesByUniversity->count() }})</p>
            <ul class="space-y-2.5 max-h-52 overflow-y-auto pr-1">
                @forelse ($feesByUniversity as $row)
                    <li class="flex items-center justify-between text-xs">
                        <div class="min-w-0 flex-1 pr-2">
                            <p class="font-semibold text-slate-700 truncate">{{ $row['university'] }}</p>
                            <p class="text-[10px] text-slate-400">{{ $row['count'] }} course(s)</p>
                        </div>
                        <span class="text-slate-800 font-bold">{{ $rupees($row['total']) }}</span>
                    </li>
                @empty
                    <li class="text-xs text-slate-400 text-center py-4">No fee structures yet</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

{{-- ─── ROW 4 — QUICK LINKS + RECENT ACTIVITY ─── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Quick Links --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-800">Quick Links</h3>
            <span class="text-[11px] text-slate-400">Shortcuts to your common tasks</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            {{-- Add Student --}}
            <a href="{{ route('students.index', ['panel' => 'create']) }}"
               class="group relative overflow-hidden rounded-xl p-4 bg-gradient-to-br from-pink-50 to-rose-50 border border-pink-100 hover:border-pink-300 hover:shadow-md transition">
                <div class="w-10 h-10 rounded-lg bg-white text-pink-600 shadow-sm flex items-center justify-center mb-2.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7zM19 8v6m3-3h-6"/></svg>
                </div>
                <p class="text-sm font-bold text-slate-800">Add Student</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Create a new student record</p>
            </a>

            {{-- Pay Fee / Update Fee --}}
            <a href="{{ route('pay-fee.index') }}"
               class="group relative overflow-hidden rounded-xl p-4 bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 hover:border-emerald-300 hover:shadow-md transition">
                <div class="w-10 h-10 rounded-lg bg-white text-emerald-600 shadow-sm flex items-center justify-center mb-2.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm font-bold text-slate-800">Collect Fee</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Post a payment for a student</p>
            </a>

            {{-- Announcement --}}
            <a href="{{ route('announcements.index') }}{{ $isAdmin ? '?panel=create' : '' }}"
               class="group relative overflow-hidden rounded-xl p-4 bg-gradient-to-br from-amber-50 to-yellow-50 border border-amber-100 hover:border-amber-300 hover:shadow-md transition">
                <div class="w-10 h-10 rounded-lg bg-white text-amber-600 shadow-sm flex items-center justify-center mb-2.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                </div>
                <p class="text-sm font-bold text-slate-800">{{ $isAdmin ? 'New Announcement' : 'Announcements' }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $isAdmin ? 'Broadcast to sub-admins' : 'See latest updates' }}</p>
            </a>

            {{-- Support --}}
            <a href="{{ route('support.index') }}{{ $isAdmin ? '' : '?panel=create' }}"
               class="group relative overflow-hidden rounded-xl p-4 bg-gradient-to-br from-sky-50 to-cyan-50 border border-sky-100 hover:border-sky-300 hover:shadow-md transition">
                <div class="w-10 h-10 rounded-lg bg-white text-sky-600 shadow-sm flex items-center justify-center mb-2.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm font-bold text-slate-800">{{ $isAdmin ? 'Review Support' : 'Contact Admin' }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $isAdmin ? 'Reply to open queries' : 'Raise a new query' }}</p>
            </a>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6 flex flex-col">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-800">Recent Activity</h3>
            <span class="inline-flex items-center gap-1 text-[11px] text-emerald-600 font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live
            </span>
        </div>
        @if ($activities->isEmpty())
            <p class="text-xs text-slate-400 text-center py-8">No activity yet</p>
        @else
            <div class="space-y-3 flex-1 overflow-y-auto max-h-80 pr-1">
                @foreach ($activities as $a)
                    <a href="{{ $a['href'] }}" class="flex items-start gap-3 group">
                        <div class="w-8 h-8 rounded-lg {{ $activityColorMap[$a['type']] ?? 'bg-slate-50 text-slate-500' }} flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                {!! $activityIconMap[$a['type']] ?? '' !!}
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-700 group-hover:text-pink-600 transition">{{ $a['title'] }}</p>
                            <p class="text-[11px] text-slate-500 truncate">{{ $a['meta'] }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $a['at']?->diffForHumans() }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- ─── ROW 5 — SUPPORT (+ ENQUIRIES for admin only) ─── --}}
<div class="grid grid-cols-1 {{ $isAdmin ? 'md:grid-cols-2' : '' }} gap-6">

    {{-- Support (both roles) --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Support Queries</h3>
                <p class="text-[11px] text-slate-500">{{ $isAdmin ? 'All queries across sub-admins' : 'Your queries' }}</p>
            </div>
            <a href="{{ route('support.index') }}" class="text-xs font-semibold text-pink-600 hover:underline">View all →</a>
        </div>

        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="rounded-xl bg-slate-50 p-3 text-center">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Total</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-800">{{ $supportStats['total'] }}</p>
            </div>
            <div class="rounded-xl bg-amber-50 p-3 text-center">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-700">Pending</p>
                <p class="mt-1 text-2xl font-extrabold text-amber-700">{{ $supportStats['pending'] }}</p>
            </div>
            <div class="rounded-xl bg-emerald-50 p-3 text-center">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-700">Replied</p>
                <p class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $supportStats['replied'] }}</p>
            </div>
        </div>

        <div class="h-32 flex items-center justify-center">
            <canvas id="supportChart"></canvas>
        </div>
    </div>

    {{-- Enquiries (admin only — sub-admin shouldn't see leads at all) --}}
    @if ($isAdmin)
        <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Enquiries</h3>
                    <p class="text-[11px] text-slate-500">Leads from the marketing site</p>
                </div>
                <a href="{{ route('enquiries.index') }}" class="text-xs font-semibold text-pink-600 hover:underline">Review →</a>
            </div>

            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="rounded-xl bg-slate-50 p-3 text-center">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Total</p>
                    <p class="mt-1 text-2xl font-extrabold text-slate-800">{{ $enquiryStats['total'] }}</p>
                </div>
                <div class="rounded-xl bg-amber-50 p-3 text-center">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-700">Pending</p>
                    <p class="mt-1 text-2xl font-extrabold text-amber-700">{{ $enquiryStats['pending'] }}</p>
                </div>
                <div class="rounded-xl bg-emerald-50 p-3 text-center">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-700">Approved</p>
                    <p class="mt-1 text-2xl font-extrabold text-emerald-700">{{ $enquiryStats['approved'] }}</p>
                </div>
            </div>

            <div class="rounded-lg bg-pink-50/40 border border-pink-100 p-3 text-center">
                <p class="text-xs text-slate-600">Visitors submitting the contact form on
                    <a href="https://ssbeducation.in" class="font-semibold text-pink-600 hover:underline">ssbeducation.in</a>
                    land here automatically.</p>
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;

    Chart.defaults.font.family = 'Inter, sans-serif';
    Chart.defaults.color = '#64748b';

    // ── Student timeline (line)
    const sCtx = document.getElementById('studentChart');
    if (sCtx) {
        const labels = @json($studentChart['labels']);
        const data   = @json($studentChart['data']);
        const grad = sCtx.getContext('2d').createLinearGradient(0, 0, 0, 200);
        grad.addColorStop(0, 'rgba(236,72,153,0.35)');
        grad.addColorStop(1, 'rgba(236,72,153,0)');
        new Chart(sCtx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Students',
                    data,
                    borderColor: '#ec4899',
                    backgroundColor: grad,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8, font: { size: 10 } }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                },
            },
        });
    }

    // ── Collection trend (14-day area chart on the fee detail row)
    const cCtx = document.getElementById('collectionChart');
    if (cCtx) {
        const labels = @json($collectionTrend['labels']);
        const data   = @json($collectionTrend['data']);
        const grad = cCtx.getContext('2d').createLinearGradient(0, 0, 0, 200);
        grad.addColorStop(0, 'rgba(16,185,129,0.35)');
        grad.addColorStop(1, 'rgba(16,185,129,0)');
        new Chart(cCtx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Collected (₹)',
                    data,
                    borderColor: '#10b981',
                    backgroundColor: grad,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => '₹' + Number(ctx.raw).toLocaleString('en-IN') } },
                },
                scales: {
                    x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8, font: { size: 10 } }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { font: { size: 10 }, callback: v => '₹' + Number(v).toLocaleString('en-IN') }, grid: { color: '#f1f5f9' } },
                },
            },
        });
    }

    // ── Support donut
    const supCtx = document.getElementById('supportChart');
    if (supCtx) {
        const pending = @json($supportStats['pending']);
        const replied = @json($supportStats['replied']);
        new Chart(supCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Replied'],
                datasets: [{
                    data: [pending, replied],
                    backgroundColor: ['#f59e0b', '#10b981'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'right', labels: { font: { size: 10 }, boxWidth: 10 } },
                },
            },
        });
    }
})();
</script>
@endsection
