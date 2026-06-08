{{-- Print-ready PDF of every credit, debit and fee-pay wallet entry
     between the chosen dates. The page auto-fires the browser's print
     dialog so the user lands in "Save as PDF" in one click. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet Transactions — SSB Education</title>
    <style>
        @page { size: A4 landscape; margin: 10mm 8mm; }
        * { box-sizing: border-box; }
        html, body {
            margin: 0; padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #111;
            font-size: 10px;
            line-height: 1.4;
            background: #f6f7f9;
        }
        .page {
            max-width: 1280px;
            margin: 0 auto;
            padding: 16px 20px 24px;
            background: #fff;
        }
        .doc-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 2px solid #db2777;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .doc-head h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            color: #111;
        }
        .doc-head .brand {
            font-size: 10px;
            color: #db2777;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-head .meta {
            text-align: right;
            font-size: 9px;
            color: #555;
            line-height: 1.5;
        }
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 12px;
            font-size: 10px;
        }
        .filters span b { color: #1e293b; font-weight: 700; }
        .summary {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin-bottom: 12px;
        }
        .stat {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 10px;
        }
        .stat .v {
            display: block;
            font-size: 15px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 2px;
            color: #db2777;
        }
        .stat .l {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .stat.credit .v { color: #047857; }
        .stat.debit  .v { color: #be123c; }
        .stat.net    .v { color: #0369a1; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 5px 6px;
            vertical-align: top;
            text-align: left;
        }
        th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        td.num, th.num { text-align: right; white-space: nowrap; }
        td.center, th.center { text-align: center; }
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .badge-credit { background: #d1fae5; color: #047857; }
        .badge-debit  { background: #fee2e2; color: #be123c; }
        .badge-mode   { background: #e0e7ff; color: #3730a3; }
        .empty {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 20px;
            text-align: center;
            color: #64748b;
            border-radius: 6px;
            font-style: italic;
        }
        .toolbar {
            position: fixed;
            top: 12px;
            right: 12px;
            display: flex;
            gap: 8px;
            z-index: 100;
        }
        .toolbar button, .toolbar a {
            background: #db2777;
            color: #fff;
            border: 0;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(0,0,0,0.18);
        }
        .toolbar a.secondary {
            background: #fff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .doc-foot {
            margin-top: 16px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
        tfoot td {
            background: #f1f5f9;
            font-weight: 700;
            color: #1e293b;
        }
        @media print {
            html, body { background: #fff; }
            .toolbar { display: none !important; }
            .page { box-shadow: none; padding: 0; }
            tr { page-break-inside: avoid; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <button type="button" onclick="window.print()">Save / Print PDF</button>
    <a href="{{ route('wallet.index') }}" class="secondary">Close</a>
</div>

<div class="page">

    {{-- ──── Document header ──── --}}
    <div class="doc-head">
        <div>
            <div class="brand">SSB Education</div>
            <h1>Wallet Transactions Report</h1>
            <div style="color:#64748b;font-size:10px;margin-top:2px;">
                Every credit, debit and fee-pay entry in the selected date range
            </div>
        </div>
        <div class="meta">
            <div><b>Generated:</b> {{ $generatedAt->format('d M Y, h:i A') }}</div>
            <div><b>By:</b> {{ $generatedBy->name }} ({{ ucfirst($generatedBy->role ?? 'user') }})</div>
            <div><b>Scope:</b> {{ $isAdmin ? 'All users (admin view)' : 'My wallet only' }}</div>
        </div>
    </div>

    {{-- ──── Active filters / range ──── --}}
    <div class="filters">
        <span><b>From:</b> {{ $from->format('d M Y') }}</span>
        <span><b>To:</b> {{ $to->format('d M Y') }}</span>
        <span><b>Mode:</b> {{ $mode === 'all' ? 'All modes' : strtoupper($mode) }}</span>
        <span><b>Type:</b>
            @if ($scope === 'credit') Credit only
            @elseif ($scope === 'debit') Debit only
            @else All (credit · debit · fee-pay)
            @endif
        </span>
        <span><b>Days:</b> {{ $from->diffInDays($to) + 1 }}</span>
    </div>

    {{-- ──── Summary tiles ──── --}}
    <div class="summary">
        <div class="stat"><span class="v">{{ $totals['count'] }}</span><span class="l">Transactions</span></div>
        <div class="stat credit"><span class="v">₹{{ number_format($totals['credit'], 2) }}</span><span class="l">Total Credit</span></div>
        <div class="stat debit"><span class="v">₹{{ number_format($totals['debit'], 2) }}</span><span class="l">Total Debit / Spent</span></div>
        <div class="stat net"><span class="v">₹{{ number_format($totals['net'], 2) }}</span><span class="l">Net Movement</span></div>
        <div class="stat"><span class="v">₹{{ number_format(\App\Models\WalletTransaction::balanceFor($generatedBy->id), 2) }}</span><span class="l">Current Wallet</span></div>
    </div>

    {{-- ──── Main ledger ──── --}}
    @if ($transactions->isEmpty())
        <div class="empty">No wallet transactions found between {{ $from->format('d M Y') }} and {{ $to->format('d M Y') }} for the chosen filters.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:30px;" class="center">#</th>
                    <th>Date &amp; Time</th>
                    <th>Wallet Owner</th>
                    <th>Mobile</th>
                    <th>Role</th>
                    <th class="center">Type</th>
                    <th class="center">Mode</th>
                    <th class="num">Amount (₹)</th>
                    <th>Note / Reason</th>
                    @if ($isAdmin)<th>Recorded By</th>@endif
                    <th>Linked Request</th>
                    <th class="num">Txn ID</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $i => $txn)
                    @php
                        $isCredit = (float) $txn->amount >= 0;
                        $absAmount = abs((float) $txn->amount);
                    @endphp
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td>
                            <div>{{ $txn->created_at?->format('d M Y') }}</div>
                            <div style="color:#64748b;font-size:9px;">{{ $txn->created_at?->format('h:i:s A') }}</div>
                        </td>
                        <td><b>{{ $txn->user?->name ?? '—' }}</b></td>
                        <td>{{ $txn->user?->mobile ?? '—' }}</td>
                        <td style="text-transform:capitalize;">{{ $txn->user?->role ?? '—' }}</td>
                        <td class="center">
                            @if ($isCredit)
                                <span class="badge badge-credit">Credit</span>
                            @else
                                <span class="badge badge-debit">Debit</span>
                            @endif
                        </td>
                        <td class="center"><span class="badge badge-mode">{{ strtoupper($txn->mode) }}</span></td>
                        <td class="num" style="color:{{ $isCredit ? '#047857' : '#be123c' }};font-weight:700;">
                            {{ $isCredit ? '+' : '−' }}{{ number_format($absAmount, 2) }}
                        </td>
                        <td style="max-width:260px;">{{ $txn->note ?: '—' }}</td>
                        @if ($isAdmin)
                            <td>
                                {{ $txn->creator?->name ?? '—' }}
                                @if ($txn->creator?->role)
                                    <div style="color:#64748b;font-size:9px;text-transform:capitalize;">{{ $txn->creator->role }}</div>
                                @endif
                            </td>
                        @endif
                        <td style="max-width:180px;">
                            @if ($txn->paymentRequest)
                                <b>{{ $txn->paymentRequest->topic }}</b>
                                <div style="color:#64748b;font-size:9px;">
                                    Requested ₹{{ number_format((float) $txn->paymentRequest->amount, 2) }}
                                    · Approved ₹{{ number_format((float) $txn->paymentRequest->approved_amount, 2) }}
                                </div>
                                @if ($txn->paymentRequest->admin_note)
                                    <div style="color:#475569;font-size:9px;font-style:italic;">{{ $txn->paymentRequest->admin_note }}</div>
                                @endif
                            @else
                                <span style="color:#94a3b8;">—</span>
                            @endif
                        </td>
                        <td class="num" style="color:#64748b;">#{{ $txn->id }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" style="text-align:right;">Totals</td>
                    <td class="num">
                        <div style="color:#047857;">+{{ number_format($totals['credit'], 2) }}</div>
                        <div style="color:#be123c;">−{{ number_format($totals['debit'], 2) }}</div>
                        <div style="border-top:1px solid #cbd5e1;margin-top:2px;padding-top:2px;">
                            Net {{ $totals['net'] >= 0 ? '+' : '' }}{{ number_format($totals['net'], 2) }}
                        </div>
                    </td>
                    <td colspan="{{ $isAdmin ? 4 : 3 }}" style="color:#64748b;font-weight:400;">
                        {{ $totals['count'] }} transaction(s) in the selected window.
                    </td>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="doc-foot">
        End of report · Generated by SSB Education portal · {{ $generatedAt->format('d M Y H:i:s') }}
        · Range: {{ $from->format('d M Y') }} – {{ $to->format('d M Y') }}
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 350);
    });
</script>

</body>
</html>
