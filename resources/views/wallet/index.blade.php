@extends('layouts.admin')

@section('title', 'Wallet - SSB Education')

@php
    /** @var string $tab */
    /** @var string $mode */
    /** @var string $search */
    /** @var \Illuminate\Support\Collection $transactions */
    /** @var array $stats */
    /** @var \Illuminate\Support\Collection $users */
    /** @var bool $isAdmin */
    /** @var \App\Models\User $authUser */

    $tabs = [
        'history'      => 'Wallet History',
        'transactions' => 'All Transactions',
    ];

    $modeChips = [
        'all'    => 'All',
        'cash'   => 'Cash',
        'upi'    => 'UPI',
        'cheque' => 'Cheque',
        'online' => 'Online',
        'neft'   => 'NEFT',
    ];

    $buildUrl = function (array $overrides) use ($tab, $mode, $search) {
        $params = array_filter(array_merge([
            'tab'  => $tab,
            'mode' => $mode === 'all' ? null : $mode,
            'q'    => $search !== '' ? $search : null,
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

        <div class="flex items-center gap-x-6 gap-y-1 text-xs text-slate-500 flex-wrap">
            <span>My Balance: <span class="text-emerald-600 font-bold ml-1">₹{{ number_format($stats['balance']) }}</span></span>
            @if ($isAdmin)
                <span>Disbursed by me: <span class="text-pink-600 font-semibold ml-1">₹{{ number_format($stats['disbursed']) }}</span></span>
                <span>Txns: <span class="text-slate-800 font-semibold ml-1">{{ $stats['transactions'] }}</span></span>
                <span>System: <span class="text-slate-800 font-semibold ml-1">₹{{ number_format($stats['system_total']) }}</span></span>
            @else
                <span>Total Credits: <span class="text-pink-600 font-semibold ml-1">₹{{ number_format($stats['received']) }}</span></span>
                <span>Txns: <span class="text-slate-800 font-semibold ml-1">{{ $stats['transactions'] }}</span></span>
            @endif
        </div>

        @if ($isAdmin)
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

        <form method="GET" action="{{ route('wallet.index') }}" class="ml-auto flex items-center gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            @if ($mode !== 'all')<input type="hidden" name="mode" value="{{ $mode }}">@endif
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
    @if ($transactions->isEmpty())
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
                        @if ($isAdmin && $tab === 'history')
                            <th class="text-left px-6 py-3">Credited To</th>
                        @elseif ($isAdmin)
                            <th class="text-left px-6 py-3">Credited To</th>
                            <th class="text-left px-6 py-3">By</th>
                        @else
                            <th class="text-left px-6 py-3">From</th>
                        @endif
                        <th class="text-left px-6 py-3">Mode</th>
                        <th class="text-left px-6 py-3">Note</th>
                        <th class="text-right px-6 py-3">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($transactions as $t)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-3 text-slate-600 whitespace-nowrap">
                                <div class="text-slate-800 font-medium">{{ $t->created_at?->format('d M Y') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $t->created_at?->format('h:i A') }}</div>
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
                            @else
                                <td class="px-6 py-3">
                                    <div class="font-medium text-slate-800">{{ $t->creator?->name ?: 'System' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $t->creator?->role === 'admin' ? 'Admin' : 'Sub-admin' }}</div>
                                </td>
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
@if ($isAdmin)
{{-- SLIDE-IN PANEL — Update Wallet --}}
<aside id="walletPanel" class="absolute inset-0 z-30 hidden" aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/30 opacity-0 transition-opacity duration-200" id="walletPanelBackdrop" onclick="WalletPanel.close()"></div>
    <div id="walletPanelCard"
         class="absolute right-0 top-0 bottom-0 w-full max-w-xl bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300 ease-out">

        <button type="button" onclick="WalletPanel.close()" aria-label="Close"
                class="absolute top-3 right-3 z-10 w-8 h-8 rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700 inline-flex items-center justify-center transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <form method="POST" action="{{ route('wallet.store') }}" class="flex-1 flex flex-col min-h-0">
            @csrf
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
    </div>
</aside>

<script>
    const WalletPanel = (function () {
        const panel    = document.getElementById('walletPanel');
        const card     = document.getElementById('walletPanelCard');
        const backdrop = document.getElementById('walletPanelBackdrop');

        function openUpdate() {
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
        return { openUpdate, close };
    })();

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !document.getElementById('walletPanel').classList.contains('hidden')) {
            WalletPanel.close();
        }
    });

    // If we redirected back with validation errors, reopen the panel.
    @if ($errors->any() && $isAdmin)
        WalletPanel.openUpdate();
    @endif

    // Dashboard / topbar quick-link support: ?panel=update opens the form.
    (function () {
        const params = new URLSearchParams(window.location.search);
        if (params.get('panel') === 'update') WalletPanel.openUpdate();
    })();
</script>
@endif
@endsection
