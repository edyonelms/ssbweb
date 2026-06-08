@extends('layouts.admin')

@section('title', 'Wallet - SSB Education')

@php
    /** @var string $tab */
    /** @var string $mode */
    /** @var string $scope */
    /** @var string $search */
    /** @var \Illuminate\Support\Collection $transactions */
    /** @var array $stats */
    /** @var \Illuminate\Support\Collection $users */
    /** @var bool $isAdmin */
    /** @var \App\Models\User $authUser */

    /** @var \Illuminate\Support\Collection $paymentRequests */
    /** @var array $requestStats */

    // For the admin this tab is the inbox of sub-admins' requests, so it
    // reads "Funds Request"; for the sub-admin it remains a CTA to ask.
    $tabs = $isAdmin ? [
        'history'      => 'Wallet History',
        'transactions' => 'All Transactions',
        'requests'     => 'Funds Request',
    ] : [
        'history'      => 'Wallet History',
        'transactions' => 'All Transactions',
        'requests'     => 'Ask Payment',
    ];

    $statusStyles = [
        'pending'  => ['bg' => 'bg-amber-50',   'text' => 'text-amber-700',   'label' => 'Pending'],
        'approved' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'label' => 'Approved'],
        'rejected' => ['bg' => 'bg-rose-50',    'text' => 'text-rose-700',    'label' => 'Rejected'],
    ];

    $modeChips = [
        'all'    => 'All',
        'cash'   => 'Cash',
        'upi'    => 'UPI',
        'cheque' => 'Cheque',
        'online' => 'Online',
        'neft'   => 'NEFT',
    ];

    // Admin-only Wallet-History scope chips: All / Self (admin's own
    // wallet) / Others (everyone else, typically sub-admins).
    $scopeChips = [
        'all'    => 'All',
        'self'   => 'Self',
        'others' => 'Others',
    ];

    $buildUrl = function (array $overrides) use ($tab, $mode, $scope, $search) {
        $params = array_filter(array_merge([
            'tab'   => $tab,
            'mode'  => $mode === 'all' ? null : $mode,
            'scope' => $scope === 'all' ? null : $scope,
            'q'     => $search !== '' ? $search : null,
        ], $overrides), fn ($v) => $v !== null && $v !== '');
        return route('wallet.index').'?'.http_build_query($params);
    };
@endphp

@section('admin-header')
<div class="sticky top-0 z-20 bg-white border-b border-slate-200">
    {{-- Title row --}}
    <div class="px-6 lg:px-10 py-3 flex flex-wrap items-center gap-x-6 gap-y-2 border-b border-slate-100">
        <div class="mr-auto">
            <h2 class="text-base font-bold text-slate-800">Wallet</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                @if ($isAdmin)
                    Manage balances across the platform — credit any user from here
                @else
                    Your wallet balance and credits received
                @endif
            </p>
        </div>

        <div class="flex items-center gap-x-4 gap-y-1 text-xs text-slate-500 flex-wrap">
            @if ($tab === 'requests')
                <span>Pending: <span class="text-amber-600 font-bold ml-1">{{ $requestStats['pending'] }}</span></span>
                <span>Approved: <span class="text-emerald-600 font-semibold ml-1">{{ $requestStats['approved'] }}</span></span>
                <span>Rejected: <span class="text-rose-600 font-semibold ml-1">{{ $requestStats['rejected'] }}</span></span>
            @else
                <span>Balance: <span class="text-emerald-600 font-bold ml-1">₹{{ number_format($stats['balance']) }}</span></span>
                @if ($isAdmin)
                    <span>Credited: <span class="text-pink-600 font-semibold ml-1">₹{{ number_format($stats['credited']) }}</span></span>
                    <span>Collected: <span class="text-violet-600 font-semibold ml-1">₹{{ number_format($stats['collected']) }}</span></span>
                @else
                    <span>Received: <span class="text-pink-600 font-semibold ml-1">₹{{ number_format($stats['received']) }}</span></span>
                    <span>Spent: <span class="text-rose-600 font-semibold ml-1">₹{{ number_format($stats['spent']) }}</span></span>
                @endif
                <span>Txns: <span class="text-slate-800 font-semibold ml-1">{{ $stats['transactions'] }}</span></span>
            @endif
        </div>

        @if ($tab === 'requests' && ! $isAdmin)
            <button type="button" onclick="WalletPanel.openAsk()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Ask Payment
            </button>
        @elseif ($isAdmin)
            <button type="button" onclick="WalletPanel.openUpdate()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Update Wallet
            </button>
        @endif
    </div>

    {{-- Tabs row --}}
    <div class="px-4 lg:px-8 flex gap-0 overflow-x-auto border-b border-slate-100" role="tablist">
        @foreach ($tabs as $key => $label)
            @php $isActive = $tab === $key; @endphp
            <a href="{{ $buildUrl(['tab' => $key]) }}"
               class="relative px-3 sm:px-4 py-2.5 text-sm font-medium whitespace-nowrap transition
                      {{ $isActive ? 'text-pink-600 font-semibold' : 'text-slate-500 hover:text-pink-600' }}">
                {{ $label }}
                @if ($isActive)
                    <span class="absolute left-3 right-3 sm:left-4 sm:right-4 bottom-0 h-0.5 rounded-full bg-pink-500"></span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Filter row --}}
    <div class="px-6 lg:px-10 py-2.5 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs">
        <div class="flex items-center gap-1.5 text-slate-500">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
            </svg>
            <span class="font-semibold text-slate-600">Filter by:</span>
        </div>

        @if ($tab !== 'requests')
            <div class="flex items-center gap-1.5">
                <span class="text-slate-500">Mode:</span>
                <div class="flex items-center gap-1 flex-wrap">
                    @foreach ($modeChips as $key => $label)
                        @php $isActive = $mode === $key; @endphp
                        <a href="{{ $buildUrl(['mode' => $key === 'all' ? null : $key]) }}"
                           class="px-3 py-1 rounded-full text-xs font-semibold transition
                                  {{ $isActive
                                        ? 'bg-pink-600 text-white shadow-sm shadow-pink-500/30'
                                        : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            @if ($isAdmin && $tab === 'history')
                <div class="flex items-center gap-1.5">
                    <span class="text-slate-500">Scope:</span>
                    <div class="flex items-center gap-1 flex-wrap">
                        @foreach ($scopeChips as $key => $label)
                            @php $isActive = $scope === $key; @endphp
                            <a href="{{ $buildUrl(['scope' => $key === 'all' ? null : $key]) }}"
                               class="px-3 py-1 rounded-full text-xs font-semibold transition border
                                      {{ $isActive
                                            ? 'bg-emerald-50 border-emerald-300 text-emerald-700'
                                            : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        <form method="GET" action="{{ route('wallet.index') }}" class="ml-auto flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if ($mode !== 'all')<input type="hidden" name="mode" value="{{ $mode }}">@endif
            @if ($scope !== 'all')<input type="hidden" name="scope" value="{{ $scope }}">@endif
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                    </svg>
                </div>
                <input type="text" name="q" value="{{ $search }}"
                       placeholder="Search name or note..."
                       class="w-56 sm:w-64 pl-7 pr-3 py-1.5 bg-white border border-slate-200 rounded-full text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 transition">
            </div>
            <button type="submit"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold bg-pink-600 hover:bg-pink-700 text-white transition">
                Search
            </button>
            @if ($search !== '')
                <a href="{{ $buildUrl(['q' => null]) }}"
                   class="px-2 py-1.5 rounded-full text-xs font-semibold text-slate-500 hover:bg-slate-100 transition" title="Clear search">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            @endif
        </form>
    </div>
</div>
@endsection

@section('admin')
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

    {{-- ─────────── REQUESTS TAB (Ask Payment) ─────────── --}}
    @if ($tab === 'requests')
        @if ($paymentRequests->isEmpty())
            <div class="px-6 py-20 text-center">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-14 h-14 rounded-full bg-pink-50 text-pink-500 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">
                        @if ($isAdmin)No payment requests yet @else No requests raised yet @endif
                    </h3>
                    <p class="text-sm text-slate-500">
                        @if ($isAdmin)
                            When sub-admins ask for funds, the requests will land here for approval.
                        @else
                            Need funds? Click <span class="font-semibold text-pink-600">Ask Payment</span> to raise a request.
                        @endif
                    </p>
                    @unless ($isAdmin)
                        <button type="button" onclick="WalletPanel.openAsk()"
                                class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Ask Payment
                        </button>
                    @endunless
                </div>
            </div>
        @else
            {{-- Compact, side-scroll-free listing. Date + requester collapse
                 into the first cell, request + decision into one amount cell,
                 and the screenshot icon sits next to the topic so the row
                 still fits on a typical 13" laptop. --}}
            <table class="w-full text-sm table-fixed">
                <thead class="text-[11px] font-semibold tracking-wider uppercase text-slate-500 border-b border-slate-200">
                    <tr>
                        @if ($isAdmin)
                            <th class="text-left px-4 py-3 w-[26%]">Date · Requested By</th>
                            <th class="text-left px-4 py-3">Topic</th>
                            <th class="text-right px-4 py-3 w-[20%]">Requested → Approved</th>
                        @else
                            <th class="text-left px-4 py-3 w-[18%]">Date</th>
                            <th class="text-left px-4 py-3">Topic</th>
                            <th class="text-right px-4 py-3 w-[22%]">Requested → Approved</th>
                        @endif
                        <th class="text-center px-3 py-3 w-[12%]">Status</th>
                        <th class="text-right px-3 py-3 w-[15%]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($paymentRequests as $r)
                        @php $st = $statusStyles[$r->status] ?? $statusStyles['pending']; @endphp
                        <tr class="hover:bg-slate-50 transition align-top">
                            <td class="px-4 py-3">
                                @if ($isAdmin)
                                    <div class="flex items-center gap-2 min-w-0">
                                        <div class="w-8 h-8 rounded-full bg-pink-50 text-pink-600 font-bold text-xs flex items-center justify-center shrink-0">{{ strtoupper(mb_substr($r->user?->name ?? '?', 0, 1)) }}</div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-slate-800 truncate">{{ $r->user?->name ?: '—' }}</div>
                                            <div class="text-[11px] text-slate-400 truncate">{{ $r->created_at?->format('d M, h:i A') }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-slate-800 font-medium whitespace-nowrap">{{ $r->created_at?->format('d M Y') }}</div>
                                    <div class="text-[11px] text-slate-400 whitespace-nowrap">{{ $r->created_at?->format('h:i A') }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-start gap-1.5 min-w-0">
                                    <div class="min-w-0 flex-1">
                                        <div class="font-medium text-slate-800 truncate" title="{{ $r->topic }}">{{ $r->topic }}</div>
                                        @if ($r->admin_note)
                                            <div class="text-[11px] text-slate-500 truncate" title="{{ $r->admin_note }}">Note: {{ $r->admin_note }}</div>
                                        @endif
                                    </div>
                                    @if ($r->screenshot_url)
                                        <a href="{{ $r->screenshot_url }}" target="_blank" rel="noopener"
                                           title="View screenshot"
                                           class="shrink-0 w-7 h-7 rounded-md text-pink-600 hover:bg-pink-50 inline-flex items-center justify-center transition">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="text-slate-700 font-medium">₹{{ number_format((float) $r->amount, 0) }}</div>
                                <div class="text-[11px]">
                                    @if ($r->approved_amount !== null)
                                        <span class="text-emerald-600 font-semibold">→ ₹{{ number_format((float) $r->approved_amount, 0) }}</span>
                                    @else
                                        <span class="text-slate-400">awaiting</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded uppercase {{ $st['bg'] }} {{ $st['text'] }}">
                                    {{ $st['label'] }}
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    @if ($isAdmin && $r->isPending())
                                        <button type="button"
                                                onclick='WalletPanel.openApprove(@json($r))'
                                                title="Approve & credit"
                                                class="w-7 h-7 rounded-md text-emerald-600 hover:bg-emerald-50 inline-flex items-center justify-center transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                        <form method="POST" action="{{ route('wallet.requests.reject', $r) }}"
                                              onsubmit="return confirmAction(this, 'Reject this payment request? The sub-admin will be notified.', 'Reject request');">
                                            @csrf
                                            <button type="submit" title="Reject"
                                                    class="w-7 h-7 rounded-md text-rose-600 hover:bg-rose-50 inline-flex items-center justify-center transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                    @if (($isAdmin || ($r->user_id === $authUser->id && $r->isPending())))
                                        <form method="POST" action="{{ route('wallet.requests.destroy', $r) }}"
                                              onsubmit="return confirmAction(this, 'Remove this request from the list?', 'Remove request');">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Delete"
                                                    class="w-7 h-7 rounded-md text-slate-500 hover:bg-slate-100 hover:text-rose-600 inline-flex items-center justify-center transition">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

    {{-- ─────────── HISTORY / TRANSACTIONS TAB ─────────── --}}
    @elseif ($transactions->isEmpty())
        <div class="px-6 py-20 text-center">
            <div class="flex flex-col items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-pink-50 text-pink-500 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12V7H5a2 2 0 010-4h14v4M3 5v14a2 2 0 002 2h16v-5M18 12a2 2 0 100 4h3v-4h-3z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">No transactions yet</h3>
                <p class="text-sm text-slate-500">
                    @if ($isAdmin)
                        Click <span class="font-semibold text-pink-600">Update Wallet</span> to credit a user.
                    @else
                        Wallet credits from the admin will appear here.
                    @endif
                </p>
                @if ($isAdmin)
                    <button type="button" onclick="WalletPanel.openUpdate()"
                            class="mt-2 inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Update Wallet
                    </button>
                @endif
            </div>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-[11px] font-semibold tracking-wider uppercase text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-6 py-3">Date</th>
                        <th class="text-left px-6 py-3">Type</th>
                        @if ($isAdmin && $tab === 'history')
                            <th class="text-left px-6 py-3">Account</th>
                        @elseif ($isAdmin)
                            <th class="text-left px-6 py-3">Account</th>
                            <th class="text-left px-6 py-3">By</th>
                            {{-- Extra column on the All Transactions tab to
                                 surface the recipient + reason side by side
                                 at a glance, so admin can read "paid to whom
                                 and why" from one column. --}}
                            <th class="text-left px-6 py-3">Payment Purpose</th>
                        @else
                            <th class="text-left px-6 py-3">From</th>
                            @if ($tab === 'transactions')
                                <th class="text-left px-6 py-3">Payment Purpose</th>
                            @endif
                        @endif
                        <th class="text-left px-6 py-3">Mode</th>
                        <th class="text-left px-6 py-3">Note</th>
                        <th class="text-right px-6 py-3">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($transactions as $t)
                        @php $isCredit = (float) $t->amount >= 0; @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 text-slate-600 whitespace-nowrap">
                                <div class="text-slate-800 font-medium">{{ $t->created_at?->format('d M Y') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $t->created_at?->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-3">
                                @if ($isCredit)
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded uppercase bg-emerald-50 text-emerald-700">Credit</span>
                                @else
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded uppercase bg-rose-50 text-rose-700">Debit</span>
                                @endif
                            </td>
                            @if ($isAdmin && $tab === 'history')
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-pink-50 text-pink-600 font-bold text-xs flex items-center justify-center">{{ strtoupper(mb_substr($t->user?->name ?? '?', 0, 1)) }}</div>
                                        <div>
                                            <div class="font-medium text-slate-800">{{ $t->user?->name ?: '—' }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $t->user?->role === 'admin' ? 'Admin' : 'Sub-admin' }}</div>
                                        </div>
                                    </div>
                                </td>
                            @elseif ($isAdmin)
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-pink-50 text-pink-600 font-bold text-xs flex items-center justify-center">{{ strtoupper(mb_substr($t->user?->name ?? '?', 0, 1)) }}</div>
                                        <div>
                                            <div class="font-medium text-slate-800">{{ $t->user?->name ?: '—' }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $t->user?->role === 'admin' ? 'Admin' : 'Sub-admin' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-slate-600">{{ $t->creator?->name ?: '—' }}</td>
                                <td class="px-6 py-3 max-w-xs">
                                    <div class="font-medium text-slate-800 truncate" title="{{ $t->user?->name }}">To: {{ $t->user?->name ?: '—' }}</div>
                                    @php $reason = $t->reason; @endphp
                                    @if ($reason !== '')
                                        <div class="text-[11px] text-slate-500 truncate" title="{{ $reason }}">
                                            For: {{ $reason }}
                                            @if ($t->paymentRequest)
                                                <span class="text-[10px] font-semibold px-1 py-0 rounded bg-emerald-50 text-emerald-700 ml-1">request</span>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-[11px] text-slate-400">No reason</div>
                                    @endif
                                </td>
                            @else
                                <td class="px-6 py-3">
                                    <div class="font-medium text-slate-800">{{ $t->creator?->name ?: 'System' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $t->creator?->role === 'admin' ? 'Admin' : 'Sub-admin' }}</div>
                                </td>
                                @if ($tab === 'transactions')
                                    <td class="px-6 py-3 max-w-xs">
                                        @php $reason = $t->reason; @endphp
                                        @if ($reason !== '')
                                            <div class="text-sm text-slate-800 truncate" title="{{ $reason }}">
                                                {{ $reason }}
                                                @if ($t->paymentRequest)
                                                    <span class="text-[10px] font-semibold px-1 py-0 rounded bg-emerald-50 text-emerald-700 ml-1">request</span>
                                                @endif
                                            </div>
                                        @else
                                            <div class="text-[11px] text-slate-400">No reason</div>
                                        @endif
                                    </td>
                                @endif
                            @endif
                            <td class="px-6 py-3">
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded uppercase
                                    @switch($t->mode)
                                        @case('cash')   bg-emerald-50 text-emerald-700 @break
                                        @case('upi')    bg-violet-50 text-violet-700 @break
                                        @case('cheque') bg-amber-50 text-amber-700 @break
                                        @case('online') bg-sky-50 text-sky-700 @break
                                        @case('neft')   bg-rose-50 text-rose-700 @break
                                        @default       bg-slate-100 text-slate-600
                                    @endswitch">
                                    {{ $t->mode }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-slate-600 max-w-xs truncate">{{ $t->note ?: '—' }}</td>
                            <td class="px-6 py-3 text-right">
                                @if ((float) $t->amount >= 0)
                                    <span class="text-emerald-600 font-bold">+ ₹{{ number_format((float) $t->amount, 2) }}</span>
                                @else
                                    <span class="text-rose-600 font-bold">− ₹{{ number_format(abs((float) $t->amount), 2) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection

@section('slide-panel')
{{-- SLIDE-IN PANEL — multi-mode (Update Wallet / Ask Payment / Approve Request) --}}
<aside id="walletPanel" class="absolute inset-0 z-30 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/30 opacity-0 transition-opacity duration-200" id="walletPanelBackdrop" onclick="WalletPanel.close()"></div>
    <div id="walletPanelCard"
         class="absolute right-0 top-0 bottom-0 w-full max-w-xl bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-out">

        <button type="button" onclick="WalletPanel.close()" aria-label="Close"
                class="absolute top-3 right-3 z-10 w-8 h-8 rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700 inline-flex items-center justify-center transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        {{-- ────────── UPDATE WALLET (admin only) ────────── --}}
        @if ($isAdmin)
        <form id="walletFormUpdate" method="POST" action="{{ route('wallet.store') }}" class="wallet-mode hidden flex-1 flex flex-col min-h-0">
            @csrf
            <div class="px-6 pt-5 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Update Wallet</h3>
                <p class="text-xs text-slate-500 mt-0.5">Credit (or debit with a negative amount) any user's wallet.</p>
            </div>
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">User <span class="text-rose-500">*</span></label>
                    <select name="user_id" required
                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                        <option value="">Select user</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}{{ $u->role === 'admin' ? ' (Admin · you)' : '' }} — {{ $u->mobile }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Amount (₹) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" required
                               value="{{ old('amount') }}"
                               placeholder="0.00"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                        @error('amount')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Mode <span class="text-rose-500">*</span></label>
                        <select name="mode" required
                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                            @foreach (\App\Models\WalletTransaction::MODES as $m)
                                <option value="{{ $m }}" {{ old('mode') === $m ? 'selected' : '' }}>{{ strtoupper($m) }}</option>
                            @endforeach
                        </select>
                        @error('mode')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Note</label>
                    <textarea name="note" rows="2" maxlength="500"
                              placeholder="Reference / remarks (optional)"
                              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">{{ old('note') }}</textarea>
                    @error('note')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <p class="text-[11px] text-slate-500 bg-slate-50 border border-slate-100 rounded-lg p-3">
                    The amount is credited to the selected user's wallet immediately. Use a negative amount to debit.
                </p>
            </div>

            <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                <button type="button" onclick="WalletPanel.close()"
                        class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Credit Wallet</button>
            </div>
        </form>
        @endif

        {{-- ────────── ASK PAYMENT (everyone can raise a request) ────────── --}}
        <form id="walletFormAsk" method="POST" action="{{ route('wallet.requests.store') }}"
              enctype="multipart/form-data" class="wallet-mode hidden flex-1 flex flex-col min-h-0">
            @csrf
            <div class="px-6 pt-5 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Ask Payment</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Raise a fund request. The admin will review and either approve (optionally adjusting the amount) or reject it.
                </p>
            </div>
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Amount (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="1" name="amount" required
                           value="{{ old('amount') }}"
                           placeholder="e.g. 5000"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                    @error('amount')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Topic <span class="text-rose-500">*</span></label>
                    <input type="text" name="topic" required maxlength="255"
                           value="{{ old('topic') }}"
                           placeholder="e.g. Marketing campaign for July"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                    @error('topic')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Screenshot (optional)</label>
                    <label class="flex items-center gap-3 px-3 py-2 border border-dashed border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 transition">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span class="text-xs text-slate-600">Choose an image (PNG/JPG/WEBP, max 4MB)</span>
                        <input type="file" name="screenshot" accept="image/png,image/jpeg,image/webp" class="hidden" data-ask-screenshot>
                    </label>
                    <p class="mt-1 text-[11px] text-slate-400" data-ask-screenshot-name></p>
                    @error('screenshot')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                <button type="button" onclick="WalletPanel.close()"
                        class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-pink-600 hover:bg-pink-700 text-white text-sm font-semibold transition">Submit Request</button>
            </div>
        </form>

        {{-- ────────── APPROVE REQUEST (admin only) ────────── --}}
        @if ($isAdmin)
        <form id="walletFormApprove" method="POST" action="" class="wallet-mode hidden flex-1 flex flex-col min-h-0">
            @csrf
            <div class="px-6 pt-5 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Approve Request</h3>
                <p class="text-xs text-slate-500 mt-0.5" id="approveSubtitle">Review the details and credit the sub-admin's wallet.</p>
            </div>
            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                <div class="rounded-xl bg-slate-50 border border-slate-100 p-4 space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Requested by</span>
                        <span class="font-semibold text-slate-800" id="approveRequestedBy">—</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Topic</span>
                        <span class="font-medium text-slate-800 truncate ml-2" id="approveTopic">—</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">Requested amount</span>
                        <span class="font-semibold text-slate-800" id="approveRequestedAmount">—</span>
                    </div>
                    <div class="text-[11px] text-slate-500 pt-1.5 border-t border-slate-200 mt-1.5">
                        Screenshot:
                        <a href="#" target="_blank" rel="noopener" id="approveScreenshotLink"
                           class="text-pink-600 hover:text-pink-700 font-medium ml-1 hidden">View</a>
                        <span id="approveNoScreenshot" class="text-slate-400 ml-1">none</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Approve Amount (₹) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="approved_amount" required
                               id="approveAmount"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                        <p class="mt-1 text-[11px] text-slate-400">Adjust up or down — defaults to requested.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Mode <span class="text-rose-500">*</span></label>
                        <select name="mode" required
                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm">
                            @foreach (\App\Models\WalletTransaction::MODES as $m)
                                <option value="{{ $m }}">{{ strtoupper($m) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Note (optional)</label>
                    <textarea name="admin_note" rows="2" maxlength="500"
                              placeholder="Add a remark visible to the sub-admin (optional)"
                              class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-pink-300/60 focus:border-pink-300/60 outline-none transition text-sm"></textarea>
                </div>
            </div>

            <div class="shrink-0 px-6 py-3 border-t border-slate-100 bg-white flex items-center justify-end gap-3">
                <button type="button" onclick="WalletPanel.close()"
                        class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">Cancel</button>
                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition">
                    Approve &amp; Credit
                </button>
            </div>
        </form>
        @endif
    </div>
</aside>

<script>
    window.WALLET_REQUEST_APPROVE_URL = @json(url('/wallet/requests/__ID__/approve'));

    const WalletPanel = (function () {
        const panel    = document.getElementById('walletPanel');
        const card     = document.getElementById('walletPanelCard');
        const backdrop = document.getElementById('walletPanelBackdrop');
        const modes    = document.querySelectorAll('.wallet-mode');

        function show(modeId) {
            modes.forEach(m => m.classList.toggle('hidden', m.id !== modeId));
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

        function openUpdate() { show('walletFormUpdate'); }
        function openAsk()    { show('walletFormAsk'); }

        function openApprove(req) {
            const form = document.getElementById('walletFormApprove');
            if (!form) return;
            form.action = window.WALLET_REQUEST_APPROVE_URL.replace('__ID__', req.id);
            form.querySelector('#approveRequestedBy').textContent     = (req.user && req.user.name) || 'Sub-admin';
            form.querySelector('#approveTopic').textContent           = req.topic || '—';
            form.querySelector('#approveRequestedAmount').textContent = '₹' + Number(req.amount || 0).toLocaleString('en-IN');
            form.querySelector('#approveAmount').value                = req.amount;
            const link = form.querySelector('#approveScreenshotLink');
            const none = form.querySelector('#approveNoScreenshot');
            if (req.screenshot_url) {
                link.href = req.screenshot_url;
                link.classList.remove('hidden');
                none.classList.add('hidden');
            } else {
                link.classList.add('hidden');
                none.classList.remove('hidden');
            }
            form.querySelector('[name="admin_note"]').value = '';
            show('walletFormApprove');
        }

        return { openUpdate, openAsk, openApprove, close };
    })();

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !document.getElementById('walletPanel').classList.contains('hidden')) {
            WalletPanel.close();
        }
    });

    // Screenshot file-name echo in the Ask Payment form.
    document.querySelectorAll('[data-ask-screenshot]').forEach(input => {
        input.addEventListener('change', () => {
            const label = input.closest('form').querySelector('[data-ask-screenshot-name]');
            if (label) label.textContent = input.files[0]?.name || '';
        });
    });

    // Reopen the right panel after a validation redirect.
    @if ($errors->any() && $isAdmin)
        WalletPanel.openUpdate();
    @elseif ($errors->any())
        WalletPanel.openAsk();
    @endif

    // Quick-link support: ?panel=update or ?panel=ask.
    (function () {
        const params = new URLSearchParams(window.location.search);
        const which = params.get('panel');
        if (which === 'update' && @json($isAdmin)) WalletPanel.openUpdate();
        else if (which === 'ask') WalletPanel.openAsk();
    })();
</script>
@endsection
