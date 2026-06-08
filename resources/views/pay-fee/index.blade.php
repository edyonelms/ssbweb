@extends('layouts.admin')

@section('title', 'Pay Fee - SSB Education')

@php
    /** @var bool $isAdmin */
    /** @var \App\Models\User $authUser */
    /** @var \Illuminate\Support\Collection $universities */
    /** @var \Illuminate\Support\Collection $students */
    /** @var int|null|string $universityId */
    /** @var int|null|string $studentId */
    /** @var string $search */
    /** @var \App\Models\Student|null $student */
    /** @var array $schedule */
    /** @var \Illuminate\Support\Collection $payments */
    /** @var array $totals */
    /** @var float $walletBalance */

    $unisOnly   = $universities->where('type', \App\Models\University::TYPE_UNIVERSITY);
    $boardsOnly = $universities->where('type', \App\Models\University::TYPE_BOARD);

    $studentIsBoard = $student?->course?->isBoard() ?? false;
    $periodWord     = $studentIsBoard ? 'Year' : 'Semester';

    $modeChips = [
        'cash'   => 'Cash',
        'upi'    => 'UPI',
        'cheque' => 'Cheque',
        'online' => 'Online',
        'neft'   => 'NEFT',
    ];

    // Group payments by batch_id so a single Pay Fee submission (that
    // overflowed into multiple semesters) reads as one logical entry in
    // the history with its splits indented under it.
    $paymentBatches = $payments
        ->groupBy(fn ($p) => $p->batch_id ?: 'single-'.$p->id)
        ->map(function ($rows) {
            $first = $rows->first();
            return [
                'id'                 => $first->batch_id ?: ('single-'.$first->id),
                'paid_at'            => $first->paid_at,
                'mode'               => $first->mode,
                'collected_by_name'  => $first->collected_by_name,
                'remark'             => $first->remark,
                'recorded_by'        => $first->recordedBy?->name,
                'total'              => (float) $rows->sum('amount'),
                'splits'             => $rows->sortBy('semester')->values(),
            ];
        })
        ->sortByDesc(fn ($b) => $b['paid_at'])
        ->values();
@endphp

@section('admin-header')
<div class="sticky top-0 z-20 bg-white border-b border-slate-200">
    <div class="px-6 lg:px-10 py-3 flex flex-wrap items-center gap-x-6 gap-y-2 border-b border-slate-100">
        <div class="mr-auto">
            <h2 class="text-base font-bold text-slate-800">Pay Fee</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Pick a university / board, find the student, then record a fee payment — extra amount overflows to the next semester automatically.
            </p>
        </div>

        {{-- Always-on PDF export — pulls every individual fee_payments
             row in the picked date window with full student / course /
             audit context. Opens the date-range modal below. --}}
        <button type="button" onclick="document.getElementById('payFeeExportModal').classList.remove('hidden')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 hover:border-pink-300 text-slate-700 hover:text-pink-600 text-sm font-semibold transition"
                title="Export every fee-pay transaction for a date range as PDF">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
            Export PDF
        </button>

        @if ($student && ! empty($schedule))
            <div class="flex items-center gap-x-6 gap-y-1 text-xs text-slate-500 flex-wrap">
                <span>Total Fee: <span class="text-slate-800 font-semibold ml-1">₹{{ number_format($totals['fee']) }}</span></span>
                <span>Paid: <span class="text-emerald-600 font-semibold ml-1">₹{{ number_format($totals['paid']) }}</span></span>
                <span>Balance: <span class="text-rose-600 font-semibold ml-1">₹{{ number_format($totals['balance']) }}</span></span>
            </div>

            <button type="button" onclick="PayFeePanel.open()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Pay Fee
            </button>
        @endif
    </div>

    {{-- Student picker row --}}
    <div class="px-6 lg:px-10 py-3 bg-slate-50/60">
        <form method="GET" action="{{ route('pay-fee.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">University / Board</label>
                <select name="university_id"
                        onchange="this.form.querySelector('[name=&quot;student_id&quot;]').value=''; this.form.submit()"
                        class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 transition min-w-[220px]">
                    <option value="">All universities / boards</option>
                    @if ($unisOnly->isNotEmpty())
                        <optgroup label="Universities">
                            @foreach ($unisOnly as $u)
                                <option value="{{ $u->id }}" {{ (string) $universityId === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if ($boardsOnly->isNotEmpty())
                        <optgroup label="Boards">
                            @foreach ($boardsOnly as $u)
                                <option value="{{ $u->id }}" {{ (string) $universityId === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>

            <div class="flex-1 min-w-[220px]">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Student</label>
                <select name="student_id"
                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 transition">
                    <option value="">Select a student</option>
                    @foreach ($students as $s)
                        <option value="{{ $s->id }}" {{ (string) $studentId === (string) $s->id ? 'selected' : '' }}>
                            {{ $s->name }}
                            @if ($s->admission_no) · #{{ $s->admission_no }} @endif
                            · {{ $s->mobile }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 min-w-[220px]">
                <label class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Search</label>
                <input type="text" name="q" value="{{ $search }}"
                       placeholder="Name, mobile, admission, parent…"
                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 transition">
            </div>

            <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
                Search
            </button>

            @if ($studentId)
                <a href="{{ route('pay-fee.index') }}"
                   class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-500 hover:bg-slate-100 transition">
                    Reset
                </a>
            @endif
        </form>
    </div>
</div>
@endsection

@section('admin')

@if (! $student)
    <div class="bg-white rounded-xl border border-slate-200 px-6 py-20 text-center">
        <div class="flex flex-col items-center gap-3">
            <div class="w-14 h-14 rounded-full bg-pink-50 text-pink-500 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12V7H5a2 2 0 010-4h14v4M3 5v14a2 2 0 002 2h16v-5M18 12a2 2 0 100 4h3v-4h-3z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-800">Pick a student to begin</h3>
            <p class="text-sm text-slate-500 max-w-md mx-auto">
                Choose a university or board to narrow the list, search by name / mobile / admission number, then select the student. Their fee schedule and transaction history will load here.
            </p>
        </div>
    </div>
@elseif (empty($schedule))
    <div class="bg-white rounded-xl border border-amber-200 px-6 py-10 text-center">
        <div class="flex flex-col items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M4.93 19h14.14a2 2 0 001.74-3l-7.07-12a2 2 0 00-3.48 0L3.19 16a2 2 0 001.74 3z"/></svg>
            </div>
            <h3 class="text-base font-bold text-slate-800">{{ $student->name }} isn't linked to a course yet</h3>
            <p class="text-sm text-slate-500 max-w-md mx-auto">
                Open the Students module, edit this student, and pick their University / Board and Course. Once a course with fees is set, payments can be recorded here.
            </p>
            <a href="{{ route('students.index') }}"
               class="mt-1 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                Go to Students
            </a>
        </div>
    </div>
@else
    {{-- STUDENT CARD --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6">
        <div class="flex flex-wrap items-start gap-4">
            <div class="w-14 h-14 rounded-full bg-pink-50 text-pink-600 font-bold text-xl flex items-center justify-center shrink-0">
                {{ strtoupper(mb_substr($student->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-base font-bold text-slate-800">{{ $student->name }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $student->mobile }}
                    @if ($student->admission_no) · #{{ $student->admission_no }} @endif
                    @if ($student->parent_name) · Parent: {{ $student->parent_name }} @endif
                </p>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-[11px]">
                    <span class="px-2 py-0.5 rounded {{ $student->university?->type === 'board' ? 'bg-emerald-50 text-emerald-700' : 'bg-pink-50 text-pink-700' }} font-semibold">
                        {{ $student->university?->name ?? '—' }}
                    </span>
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold">
                        {{ $student->course?->name ?? '—' }}
                    </span>
                    <span class="text-slate-500">
                        {{ rtrim(rtrim(number_format((float) $student->course?->duration_years, 1), '0'), '.') }} yrs
                        @if (! $studentIsBoard)
                            · {{ $student->course?->semesterCount() }} sem
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- PERIOD-WISE FEE BREAKDOWN --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 sm:px-6 py-3 border-b border-slate-100 flex items-center gap-3">
            <h3 class="text-sm font-bold text-slate-800">Fee Schedule</h3>
            <span class="text-[11px] text-slate-500">{{ $studentIsBoard ? 'Per-year' : 'Per-semester' }} breakdown — overflow on a single payment moves forward automatically.</span>
        </div>
        <table class="w-full text-sm">
            <thead class="text-[11px] font-semibold tracking-wider uppercase text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="text-left px-5 sm:px-6 py-3">{{ $periodWord }}</th>
                    <th class="text-right px-4 py-3">Fee</th>
                    <th class="text-right px-4 py-3">Paid</th>
                    <th class="text-right px-4 py-3">Balance</th>
                    <th class="text-left px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($schedule as $row)
                    @php
                        $isPaid = $row['balance'] <= 0;
                        $isPartial = $row['paid'] > 0 && $row['balance'] > 0;
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-5 sm:px-6 py-3">
                            <div class="font-medium text-slate-800">{{ $row['label'] }}</div>
                        </td>
                        <td class="px-4 py-3 text-right text-slate-700">₹{{ number_format($row['fee'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-emerald-600 font-medium">₹{{ number_format($row['paid'], 2) }}</td>
                        <td class="px-4 py-3 text-right {{ $isPaid ? 'text-slate-400' : 'text-rose-600 font-semibold' }}">₹{{ number_format($row['balance'], 2) }}</td>
                        <td class="px-4 py-3">
                            @if ($isPaid)
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded uppercase bg-emerald-50 text-emerald-700">Paid</span>
                            @elseif ($isPartial)
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded uppercase bg-amber-50 text-amber-700">Partial</span>
                            @else
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded uppercase bg-slate-100 text-slate-600">Due</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-slate-200 bg-slate-50/60">
                <tr>
                    <td class="px-5 sm:px-6 py-3 font-bold text-slate-800">Total</td>
                    <td class="px-4 py-3 text-right font-bold text-slate-800">₹{{ number_format($totals['fee'], 2) }}</td>
                    <td class="px-4 py-3 text-right font-bold text-emerald-700">₹{{ number_format($totals['paid'], 2) }}</td>
                    <td class="px-4 py-3 text-right font-bold {{ $totals['balance'] <= 0 ? 'text-slate-400' : 'text-rose-700' }}">₹{{ number_format($totals['balance'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- TRANSACTION HISTORY --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 sm:px-6 py-3 border-b border-slate-100 flex items-center justify-between gap-3">
            <h3 class="text-sm font-bold text-slate-800">Fee Transactions</h3>
            <span class="text-[11px] text-slate-500">{{ $paymentBatches->count() }} entr{{ $paymentBatches->count() === 1 ? 'y' : 'ies' }}</span>
        </div>

        @if ($paymentBatches->isEmpty())
            <div class="px-5 py-12 text-center text-sm text-slate-500">
                No payments yet — use <span class="font-semibold text-pink-600">Pay Fee</span> above to record the first one.
            </div>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($paymentBatches as $batch)
                    <li class="px-5 sm:px-6 py-3">
                        <div class="flex items-start flex-wrap gap-3">
                            <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-emerald-700">₹{{ number_format($batch['total'], 2) }}</span>
                                    <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded uppercase
                                        @switch($batch['mode'])
                                            @case('cash')   bg-emerald-50 text-emerald-700 @break
                                            @case('upi')    bg-violet-50 text-violet-700 @break
                                            @case('cheque') bg-amber-50 text-amber-700 @break
                                            @case('online') bg-sky-50 text-sky-700 @break
                                            @case('neft')   bg-rose-50 text-rose-700 @break
                                            @default       bg-slate-100 text-slate-600
                                        @endswitch">
                                        {{ $batch['mode'] }}
                                    </span>
                                    <span class="text-[11px] text-slate-500">
                                        {{ $batch['paid_at']?->format('d M Y · h:i A') }}
                                    </span>
                                </div>
                                <div class="mt-1 text-xs text-slate-600">
                                    Collected by <span class="font-medium text-slate-800">{{ $batch['collected_by_name'] }}</span>
                                    @if ($batch['recorded_by'])
                                        <span class="text-slate-400">·</span> Recorded by {{ $batch['recorded_by'] }}
                                    @endif
                                </div>
                                @if ($batch['remark'])
                                    <div class="mt-0.5 text-xs text-slate-500 italic">"{{ $batch['remark'] }}"</div>
                                @endif

                                @if ($batch['splits']->count() > 1 || $batch['splits']->first()->semester !== null)
                                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                        @foreach ($batch['splits'] as $split)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] bg-slate-100 text-slate-700">
                                                {{ $split->semester_label }}
                                                <span class="font-semibold text-slate-800">₹{{ number_format((float) $split->amount, 0) }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            @if ($isAdmin)
                                <div class="shrink-0 flex items-center gap-1">
                                    @foreach ($batch['splits'] as $split)
                                        <form method="POST" action="{{ route('pay-fee.payments.destroy', $split) }}"
                                              onsubmit="return confirmAction(this, 'Remove this fee entry ({{ $split->semester_label }} · ₹{{ number_format((float) $split->amount, 0) }})?', 'Remove entry');">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Remove {{ $split->semester_label }} entry"
                                                    class="w-7 h-7 rounded-md text-slate-400 hover:bg-rose-50 hover:text-rose-600 inline-flex items-center justify-center transition">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
@endsection

@section('slide-panel')
@if ($student && ! empty($schedule))
{{-- PAY FEE SLIDE-IN PANEL --}}
<aside id="payFeePanel" class="absolute inset-0 z-30 hidden" aria-hidden="true">
    <div id="payFeeBackdrop" class="absolute inset-0 bg-slate-900/30 opacity-0 transition-opacity duration-200" onclick="PayFeePanel.close()"></div>
    <div id="payFeeCard"
         class="absolute right-0 top-0 bottom-0 w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-out">

        <button type="button" onclick="PayFeePanel.close()" aria-label="Close"
                class="absolute top-3 right-3 z-10 w-8 h-8 rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700 inline-flex items-center justify-center transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <form method="POST" action="{{ route('pay-fee.store') }}" class="flex-1 flex flex-col min-h-0">
            @csrf
            <input type="hidden" name="student_id" value="{{ $student->id }}">

            <div class="px-5 pt-5 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Record Fee Payment</h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ $student->name }} · {{ $student->course?->name }}</p>
            </div>

            <div class="flex-1 overflow-y-auto p-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Starting {{ $periodWord }} <span class="text-rose-500">*</span></label>
                    @php
                        // Default to the first period still owing.
                        $defaultSem = collect($schedule)->firstWhere('balance', '>', 0)['semester'] ?? ($schedule[0]['semester'] ?? 1);
                    @endphp
                    <select name="start_semester" required
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                        @foreach ($schedule as $row)
                            <option value="{{ $row['semester'] }}" {{ $row['semester'] === $defaultSem ? 'selected' : '' }}>
                                {{ $row['label'] }}
                                @if ($row['balance'] > 0)
                                    — outstanding ₹{{ number_format($row['balance'], 0) }}
                                @else
                                    — paid
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('start_semester')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    <p class="mt-1 text-[11px] text-slate-400">Overflow rolls into the next unpaid {{ strtolower($periodWord) }} automatically.</p>
                </div>

                @php $maxAllowed = min($totals['balance'], $walletBalance); @endphp
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Amount (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01"
                           max="{{ $maxAllowed > 0 ? number_format($maxAllowed, 2, '.', '') : '' }}"
                           name="amount" required
                           value="{{ old('amount') }}"
                           placeholder="e.g. 2000"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                    <div class="mt-1 flex items-center justify-between text-[11px]">
                        <span class="text-slate-500">
                            Wallet balance: <span class="font-semibold {{ $walletBalance > 0 ? 'text-emerald-600' : 'text-rose-600' }}">₹{{ number_format($walletBalance, 2) }}</span>
                        </span>
                        @if ($walletBalance <= 0)
                            <span class="text-rose-600 font-semibold">Top up your wallet to record payments.</span>
                        @endif
                    </div>
                    @error('amount')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mode <span class="text-rose-500">*</span></label>
                    <select name="mode" required
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                        @foreach ($modeChips as $k => $label)
                            <option value="{{ $k }}" {{ old('mode') === $k ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('mode')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Collected By <span class="text-rose-500">*</span></label>
                    <input type="text" name="collected_by_name" required maxlength="255"
                           value="{{ old('collected_by_name', $authUser->name) }}"
                           placeholder="Name of person who took the cash"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                    @error('collected_by_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Remark</label>
                    <textarea name="remark" rows="2" maxlength="500"
                              placeholder="Reference / remarks (optional)"
                              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">{{ old('remark') }}</textarea>
                    @error('remark')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="rounded-xl bg-pink-50/60 border border-pink-100 p-3 text-[11px] text-slate-600 space-y-0.5">
                    <div class="flex items-center justify-between">
                        <span>Outstanding total</span>
                        <span class="font-semibold text-rose-600">₹{{ number_format($totals['balance'], 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Your wallet balance</span>
                        <span class="font-semibold {{ $walletBalance > 0 ? 'text-emerald-600' : 'text-rose-600' }}">₹{{ number_format($walletBalance, 2) }}</span>
                    </div>
                    <div class="text-slate-400">
                        Capped at your wallet balance — this amount is debited from your wallet when recorded. Anything above the picked {{ strtolower($periodWord) }}'s balance is auto-applied to the following one.
                    </div>
                </div>
            </div>

            <div class="shrink-0 px-5 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                <button type="button" onclick="PayFeePanel.close()"
                        class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Record Payment</button>
            </div>
        </form>
    </div>
</aside>

<script>
    const PayFeePanel = (function () {
        const panel    = document.getElementById('payFeePanel');
        const card     = document.getElementById('payFeeCard');
        const backdrop = document.getElementById('payFeeBackdrop');

        function open() {
            panel.classList.remove('hidden');
            panel.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(() => {
                backdrop.classList.add('opacity-100');
                backdrop.classList.remove('opacity-0');
                card.classList.remove('translate-x-full');
            });
        }
        function close() {
            backdrop.classList.remove('opacity-100');
            backdrop.classList.add('opacity-0');
            card.classList.add('translate-x-full');
            setTimeout(() => {
                panel.classList.add('hidden');
                panel.setAttribute('aria-hidden', 'true');
            }, 250);
        }
        return { open, close };
    })();

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !document.getElementById('payFeePanel').classList.contains('hidden')) {
            PayFeePanel.close();
        }
    });

    @if ($errors->any())
        PayFeePanel.open();
    @endif
</script>
@endif

{{-- ─────────── PDF EXPORT MODAL — date-range + filters ───────────
     Submits via GET to /pay-fee/export which renders a print-ready
     page and auto-fires the browser print dialog. --}}
<div id="payFeeExportModal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-slate-900/50" onclick="document.getElementById('payFeeExportModal').classList.add('hidden')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="px-6 pt-5 pb-3 border-b border-slate-100 flex items-start justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800">Export Fee Payments</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Every individual fee-pay transaction in the chosen window, with full student &amp; course details.
                </p>
            </div>
            <button type="button" onclick="document.getElementById('payFeeExportModal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-700 transition" title="Close">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="GET" action="{{ route('pay-fee.export') }}" target="_blank" rel="noopener" class="px-6 py-4 space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">From Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="from" required
                           value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">To Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="to" required
                           value="{{ now()->format('Y-m-d') }}"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">University / Board</label>
                <select name="university_id"
                        class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                    <option value="">All universities &amp; boards</option>
                    @if ($unisOnly->isNotEmpty())
                        <optgroup label="Universities">
                            @foreach ($unisOnly as $u)
                                <option value="{{ $u->id }}" {{ (string) $universityId === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if ($boardsOnly->isNotEmpty())
                        <optgroup label="Boards">
                            @foreach ($boardsOnly as $u)
                                <option value="{{ $u->id }}" {{ (string) $universityId === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mode</label>
                    <select name="mode"
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                        <option value="all">All modes</option>
                        @foreach (\App\Models\FeePayment::MODES as $m)
                            <option value="{{ $m }}">{{ strtoupper($m) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Single Student (optional)</label>
                    <select name="student_id"
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                        <option value="">Every student</option>
                        @foreach ($students as $s)
                            <option value="{{ $s->id }}" {{ (string) $studentId === (string) $s->id ? 'selected' : '' }}>
                                {{ $s->name }}@if ($s->admission_no) · #{{ $s->admission_no }} @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="bg-pink-50 border border-pink-100 rounded-lg px-3 py-2 text-[11px] text-pink-700 leading-snug">
                A print-ready page opens in a new tab. Use your browser's print dialog to <b>Save as PDF</b>.
            </div>

            <div class="flex items-center justify-end gap-3 pt-1">
                <button type="button" onclick="document.getElementById('payFeeExportModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                    Generate PDF
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
