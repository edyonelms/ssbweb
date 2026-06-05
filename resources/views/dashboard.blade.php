@extends('layouts.admin')

@section('title', 'Dashboard - SSB Education')

@php
    /** @var \App\Models\User $user */
    /** @var bool $isAdmin */
    /** @var array $studentCounts */
    /** @var array $studentChart */
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
    ];

    $activityColorMap = [
        'student'      => 'bg-pink-50 text-pink-600',
        'announcement' => 'bg-amber-50 text-amber-600',
        'support'      => 'bg-sky-50 text-sky-600',
        'fee'          => 'bg-emerald-50 text-emerald-600',
        'university'   => 'bg-violet-50 text-violet-600',
    ];
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

{{-- ─── ROW 1 — QUICK LINKS + RECENT ACTIVITY ─── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Quick Links --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6">
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

            {{-- Update / View Fee --}}
            <a href="{{ route('master.index', ['tab' => 'fees']) }}"
               class="group relative overflow-hidden rounded-xl p-4 bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 hover:border-emerald-300 hover:shadow-md transition">
                <div class="w-10 h-10 rounded-lg bg-white text-emerald-600 shadow-sm flex items-center justify-center mb-2.5">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm font-bold text-slate-800">{{ $isAdmin ? 'Update Fee' : 'View Fees' }}</p>
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $isAdmin ? 'Adjust per-semester fees' : 'See fee structures' }}</p>
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
    <div class="bg-white rounded-xl border border-slate-200 p-6 flex flex-col">
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

{{-- ─── ROW 2 — STUDENT ANALYTICS ─── --}}
<div class="bg-white rounded-xl border border-slate-200 p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Students Added</h3>
            <p class="text-[11px] text-slate-500">{{ $isAdmin ? 'All students added on the platform' : 'Students you added' }}</p>
        </div>
        <div class="flex items-center gap-1 flex-wrap">
            @foreach ($periodLabels as $key => $label)
                @php $isActive = $period === $key; @endphp
                <a href="{{ route('dashboard', ['student_period' => $key]) }}"
                   class="px-3 py-1 rounded-full text-xs font-semibold transition
                          {{ $isActive ? 'bg-pink-600 text-white shadow-sm shadow-pink-500/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        @php
            $cards = [
                'today'     => ['label' => 'Today',         'count' => $studentCounts['today']],
                'yesterday' => ['label' => 'Yesterday',     'count' => $studentCounts['yesterday']],
                '7'         => ['label' => 'Last 7 days',   'count' => $studentCounts['7']],
                '15'        => ['label' => 'Last 15 days',  'count' => $studentCounts['15']],
                '30'        => ['label' => 'Last 30 days',  'count' => $studentCounts['30']],
            ];
        @endphp
        @foreach ($cards as $key => $card)
            @php $isActive = $period === $key; @endphp
            <div class="rounded-xl border p-3.5 transition
                        {{ $isActive ? 'bg-pink-50/60 border-pink-200' : 'bg-slate-50/60 border-slate-100' }}">
                <p class="text-[10px] font-semibold uppercase tracking-wider {{ $isActive ? 'text-pink-600' : 'text-slate-500' }}">{{ $card['label'] }}</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-800">{{ $card['count'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="border-t border-slate-100 pt-5">
        <p class="text-[11px] text-slate-500 font-semibold mb-2">Daily breakdown · last 30 days</p>
        <div class="h-44">
            <canvas id="studentChart"></canvas>
        </div>
    </div>
</div>

{{-- ─── ROW 3 — FEE ANALYTICS ─── --}}
<div class="bg-white rounded-xl border border-slate-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Fee Analytics</h3>
            <p class="text-[11px] text-slate-500">Registration and course fees across universities</p>
        </div>
        @if ($isAdmin)
            <a href="{{ route('master.index', ['tab' => 'fees']) }}" class="text-xs font-semibold text-pink-600 hover:underline">Manage →</a>
        @endif
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="rounded-xl border border-slate-100 bg-gradient-to-br from-emerald-50 to-teal-50 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-700">Registration Pool</p>
            <p class="mt-1 text-xl font-extrabold text-slate-800">₹{{ number_format($totalRegistrationFee) }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Across all universities & boards</p>
        </div>
        <div class="rounded-xl border border-slate-100 bg-gradient-to-br from-pink-50 to-rose-50 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-pink-700">Total Course Fees</p>
            <p class="mt-1 text-xl font-extrabold text-slate-800">₹{{ number_format($totalCourseFees) }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Sum of fee × semesters per course</p>
        </div>
        <div class="rounded-xl border border-slate-100 bg-gradient-to-br from-violet-50 to-fuchsia-50 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-violet-700">Fee Structures</p>
            <p class="mt-1 text-xl font-extrabold text-slate-800">{{ $totalCourses }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Courses with a fee set</p>
        </div>
        <div class="rounded-xl border border-slate-100 bg-gradient-to-br from-amber-50 to-yellow-50 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-700">Avg / Structure</p>
            <p class="mt-1 text-xl font-extrabold text-slate-800">₹{{ $totalCourses > 0 ? number_format($totalCourseFees / $totalCourses) : 0 }}</p>
            <p class="text-[10px] text-slate-500 mt-0.5">Average total fee per course</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 border-t border-slate-100 pt-5">
        <div class="lg:col-span-2">
            <p class="text-[11px] text-slate-500 font-semibold mb-2">Total course fees · by university (top {{ $feesByUniversity->count() }})</p>
            <div class="h-56">
                <canvas id="feeChart"></canvas>
            </div>
        </div>
        <div>
            <p class="text-[11px] text-slate-500 font-semibold mb-2">Breakdown</p>
            <ul class="space-y-2.5">
                @forelse ($feesByUniversity as $row)
                    <li class="flex items-center justify-between text-xs">
                        <div class="min-w-0 flex-1 pr-2">
                            <p class="font-semibold text-slate-700 truncate">{{ $row['university'] }}</p>
                            <p class="text-[10px] text-slate-400">{{ $row['count'] }} course(s)</p>
                        </div>
                        <span class="text-slate-800 font-bold">₹{{ number_format($row['total']) }}</span>
                    </li>
                @empty
                    <li class="text-xs text-slate-400 text-center py-4">No fee data yet</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

{{-- ─── ROW 4 — SUPPORT + ENQUIRIES ─── --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    {{-- Support --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
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

    {{-- Enquiries --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Enquiries</h3>
                <p class="text-[11px] text-slate-500">External enquiries pipeline</p>
            </div>
            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-slate-100 text-slate-500">Soon</span>
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

        <div class="rounded-lg bg-slate-50/60 border border-dashed border-slate-200 p-4 text-center">
            <p class="text-xs text-slate-500">The enquiries module is wired into this dashboard slot. Once enquiries data is captured it will appear here.</p>
        </div>
    </div>
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

    // ── Fee by university (horizontal bar)
    const fCtx = document.getElementById('feeChart');
    if (fCtx) {
        new Chart(fCtx, {
            type: 'bar',
            data: {
                labels: @json($feeChart['labels']),
                datasets: [{
                    label: 'Total fees (₹)',
                    data: @json($feeChart['data']),
                    backgroundColor: [
                        '#ec4899', '#a855f7', '#22c55e', '#f59e0b', '#0ea5e9', '#f43f5e',
                    ],
                    borderRadius: 6,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { font: { size: 10 }, callback: v => '₹' + Number(v).toLocaleString('en-IN') }, grid: { color: '#f1f5f9' } },
                    y: { ticks: { font: { size: 10 } }, grid: { display: false } },
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
