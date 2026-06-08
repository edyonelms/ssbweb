{{-- Print-ready PDF of every individual fee payment in the chosen
     date range, with full student / course / audit details. Auto-fires
     the browser print dialog so the user can Save as PDF instantly. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Payments Report — SSB Education</title>
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
            font-size: 16px;
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
        .stat.green .v { color: #047857; }
        .stat.indigo .v { color: #4338ca; }
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 5px 6px;
            vertical-align: top;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }
        th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
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
        .badge-mode  { background: #e0e7ff; color: #3730a3; }
        .badge-uni   { background: #fce7f3; color: #be185d; }
        .badge-board { background: #d1fae5; color: #047857; }
        .badge-reg   { background: #fef3c7; color: #92400e; }
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
        .mode-breakdown {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
            margin-bottom: 12px;
            font-size: 10px;
        }
        .mode-breakdown .chip {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 4px 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .mode-breakdown .chip b { color: #1e293b; }
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
    <a href="{{ route('pay-fee.index') }}" class="secondary">Close</a>
</div>

<div class="page">

    {{-- ──── Document header ──── --}}
    <div class="doc-head">
        <div>
            <div class="brand">SSB Education</div>
            <h1>Fee Payments Report</h1>
            <div style="color:#64748b;font-size:10px;margin-top:2px;">
                Every single fee-pay transaction with student, course and audit context
            </div>
        </div>
        <div class="meta">
            <div><b>Generated:</b> {{ $generatedAt->format('d M Y, h:i A') }}</div>
            <div><b>By:</b> {{ $generatedBy->name }} ({{ ucfirst($generatedBy->role ?? 'user') }})</div>
            <div><b>Scope:</b> {{ $isAdmin ? 'All recorders (admin view)' : 'My collections only' }}</div>
        </div>
    </div>

    {{-- ──── Active filters / range ──── --}}
    @php
        $activeUni = $universityId ? $universities->firstWhere('id', (int) $universityId) : null;
    @endphp
    <div class="filters">
        <span><b>From:</b> {{ $from->format('d M Y') }}</span>
        <span><b>To:</b> {{ $to->format('d M Y') }}</span>
        <span><b>Mode:</b> {{ $mode === 'all' ? 'All modes' : strtoupper($mode) }}</span>
        <span><b>University / Board:</b> {{ $activeUni?->name ?? 'All' }}</span>
        <span><b>Student:</b> {{ $studentId ? '#'.$studentId : 'All students' }}</span>
        <span><b>Days:</b> {{ $from->diffInDays($to) + 1 }}</span>
    </div>

    {{-- ──── Summary tiles ──── --}}
    @php
        $uniqueStudents = $payments->pluck('student_id')->unique()->count();
        $uniqueBatches  = $payments->pluck('batch_id')->filter()->unique()->count();
    @endphp
    <div class="summary">
        <div class="stat"><span class="v">{{ $totals['count'] }}</span><span class="l">Individual Rows</span></div>
        <div class="stat indigo"><span class="v">{{ $uniqueBatches }}</span><span class="l">Pay-Fee Submissions</span></div>
        <div class="stat"><span class="v">{{ $uniqueStudents }}</span><span class="l">Students Covered</span></div>
        <div class="stat green"><span class="v">₹{{ number_format($totals['amount'], 2) }}</span><span class="l">Total Collected</span></div>
        <div class="stat"><span class="v">₹{{ $totals['count'] > 0 ? number_format($totals['amount'] / max($totals['count'], 1), 2) : '0.00' }}</span><span class="l">Avg per Row</span></div>
    </div>

    {{-- ──── Mode breakdown ──── --}}
    @if ($totals['by_mode']->isNotEmpty())
        <div class="mode-breakdown">
            <span style="font-weight:700;color:#475569;">By Mode:</span>
            @foreach ($totals['by_mode'] as $modeKey => $info)
                <span class="chip">
                    <span class="badge badge-mode">{{ strtoupper($modeKey) }}</span>
                    <span><b>{{ $info['count'] }}</b> txn · <b>₹{{ number_format($info['amount'], 2) }}</b></span>
                </span>
            @endforeach
        </div>
    @endif

    {{-- ──── Main ledger ──── --}}
    @if ($payments->isEmpty())
        <div class="empty">No fee payments found between {{ $from->format('d M Y') }} and {{ $to->format('d M Y') }} for the chosen filters.</div>
    @else
        <table>
            <colgroup>
                <col style="width:24px;">  {{-- # --}}
                <col style="width:78px;">  {{-- Paid At --}}
                <col style="width:110px;"> {{-- Student --}}
                <col style="width:65px;">  {{-- Adm No --}}
                <col style="width:70px;">  {{-- Mobile --}}
                <col style="width:90px;">  {{-- University --}}
                <col style="width:90px;">  {{-- Course --}}
                <col style="width:55px;">  {{-- Period --}}
                <col style="width:50px;">  {{-- Mode --}}
                <col style="width:70px;">  {{-- Amount --}}
                <col style="width:75px;">  {{-- Collected By --}}
                <col style="width:70px;">  {{-- Recorded By --}}
                <col style="width:100px;"> {{-- Remark --}}
                <col style="width:75px;">  {{-- Batch / Txn --}}
            </colgroup>
            <thead>
                <tr>
                    <th class="center">#</th>
                    <th>Paid At</th>
                    <th>Student</th>
                    <th>Adm. No</th>
                    <th>Mobile</th>
                    <th>University&nbsp;/&nbsp;Board</th>
                    <th>Course</th>
                    <th class="center">Period</th>
                    <th class="center">Mode</th>
                    <th class="num">Amount (₹)</th>
                    <th>Collected By (Name)</th>
                    <th>Recorded By</th>
                    <th>Remark</th>
                    <th>Batch / Txn ID</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $i => $p)
                    @php
                        $student = $p->student;
                        $course  = $student?->course;
                        $uni     = $student?->university;
                        $isBoard = $uni?->type === \App\Models\University::TYPE_BOARD;
                        $periodLabel = $p->semester === 0
                            ? 'Registration'
                            : ($isBoard ? 'Year '.$p->semester : 'Sem '.$p->semester);
                    @endphp
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td>
                            <div>{{ $p->paid_at?->format('d M Y') }}</div>
                            <div style="color:#64748b;font-size:9px;">{{ $p->paid_at?->format('h:i:s A') }}</div>
                        </td>
                        <td>
                            <b>{{ $student?->name ?? '—' }}</b>
                            @if ($student?->father_name)
                                <div style="color:#64748b;font-size:9px;">S/o {{ $student->father_name }}</div>
                            @elseif ($student?->parent_name)
                                <div style="color:#64748b;font-size:9px;">P: {{ $student->parent_name }}</div>
                            @endif
                        </td>
                        <td>{{ $student?->admission_no ?? '—' }}</td>
                        <td>
                            {{ $student?->mobile ?? '—' }}
                            @if ($student?->email)
                                <div style="color:#64748b;font-size:9px;">{{ $student->email }}</div>
                            @endif
                        </td>
                        <td>
                            {{ $uni?->name ?? '—' }}
                            @if ($uni)
                                @if ($isBoard)
                                    <span class="badge badge-board" style="margin-left:2px;">Board</span>
                                @else
                                    <span class="badge badge-uni" style="margin-left:2px;">Uni</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            {{ $course?->name ?? '—' }}
                            @if ($course)
                                <div style="color:#64748b;font-size:9px;">
                                    {{ rtrim(rtrim(number_format((float) $course->duration_years, 1), '0'), '.') }} yrs
                                    @if ($course->mode) · {{ $course->mode }} @endif
                                </div>
                            @endif
                        </td>
                        <td class="center">
                            @if ($p->semester === 0)
                                <span class="badge badge-reg">Reg.</span>
                            @else
                                <b>{{ $periodLabel }}</b>
                            @endif
                        </td>
                        <td class="center"><span class="badge badge-mode">{{ strtoupper($p->mode) }}</span></td>
                        <td class="num" style="color:#047857;font-weight:700;">{{ number_format((float) $p->amount, 2) }}</td>
                        <td>{{ $p->collected_by_name ?: '—' }}</td>
                        <td>
                            {{ $p->recordedBy?->name ?? '—' }}
                            @if ($p->recordedBy?->role)
                                <div style="color:#64748b;font-size:9px;text-transform:capitalize;">{{ $p->recordedBy->role }}</div>
                            @endif
                        </td>
                        <td style="color:#475569;">{{ $p->remark ?: '—' }}</td>
                        <td style="font-size:9px;color:#64748b;">
                            <div>Row #{{ $p->id }}</div>
                            @if ($p->batch_id)
                                <div title="{{ $p->batch_id }}">B: {{ \Illuminate\Support\Str::limit($p->batch_id, 12, '…') }}</div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="9" style="text-align:right;">Total Collected</td>
                    <td class="num" style="color:#047857;">₹{{ number_format($totals['amount'], 2) }}</td>
                    <td colspan="4" style="color:#64748b;font-weight:400;">
                        {{ $totals['count'] }} row(s) · {{ $uniqueBatches }} pay-fee submission(s) · {{ $uniqueStudents }} student(s)
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
