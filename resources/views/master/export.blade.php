{{-- Standalone print-ready dump of the full Master Data set.
     Auto-fires the browser print dialog on load so the user can
     "Save as PDF" in one click. Designed to print legibly on A4. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data Export — SSB Education</title>
    <style>
        @page { size: A4; margin: 12mm 10mm; }
        * { box-sizing: border-box; }
        html, body {
            margin: 0; padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #111;
            font-size: 11px;
            line-height: 1.4;
            background: #f6f7f9;
        }
        .page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 18px 22px 28px;
            background: #fff;
        }
        .doc-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            border-bottom: 2px solid #db2777;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .doc-head h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            color: #111;
            letter-spacing: 0.2px;
        }
        .doc-head .meta {
            text-align: right;
            font-size: 10px;
            color: #555;
            line-height: 1.5;
        }
        .doc-head .brand {
            font-size: 11px;
            color: #db2777;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .section {
            margin-top: 18px;
            page-break-inside: auto;
        }
        .section-title {
            font-size: 14px;
            font-weight: 800;
            color: #db2777;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 0 0 6px;
            padding-bottom: 4px;
            border-bottom: 1px solid #fbcfe8;
        }
        .section-sub {
            color: #666;
            font-size: 10px;
            margin-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 5px 7px;
            vertical-align: top;
            text-align: left;
        }
        th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        td.num, th.num { text-align: right; white-space: nowrap; }
        td.center, th.center { text-align: center; }
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .badge-uni   { background: #fce7f3; color: #be185d; }
        .badge-board { background: #d1fae5; color: #047857; }
        .badge-yes   { background: #d1fae5; color: #047857; }
        .badge-no    { background: #e2e8f0; color: #475569; }
        .uni-block {
            margin-top: 14px;
            padding: 10px 12px;
            background: #fff7fb;
            border: 1px solid #fbcfe8;
            border-radius: 6px;
            page-break-inside: avoid;
        }
        .uni-block + .uni-block { margin-top: 12px; }
        .uni-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 6px;
        }
        .uni-header h3 {
            margin: 0;
            font-size: 13px;
            font-weight: 800;
            color: #111;
        }
        .uni-header .uni-meta {
            font-size: 10px;
            color: #555;
            text-align: right;
            line-height: 1.5;
        }
        .kv {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 4px 12px;
            margin-top: 4px;
            font-size: 10px;
            color: #475569;
        }
        .kv span b { color: #1e293b; font-weight: 700; }
        .summary {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            margin-bottom: 10px;
        }
        .stat {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 8px;
        }
        .stat .v {
            display: block;
            font-size: 16px;
            font-weight: 800;
            color: #db2777;
            line-height: 1;
            margin-bottom: 2px;
        }
        .stat .l {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .empty {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 10px 12px;
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
            margin-top: 22px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
        }
        @media print {
            html, body { background: #fff; }
            .toolbar { display: none !important; }
            .page { box-shadow: none; padding: 0; }
            tr, .uni-block { page-break-inside: avoid; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <button type="button" onclick="window.print()">Save / Print PDF</button>
    <a href="{{ route('master.index') }}" class="secondary">Close</a>
</div>

<div class="page">

    {{-- ──── Document header ──── --}}
    <div class="doc-head">
        <div>
            <div class="brand">SSB Education</div>
            <h1>Master Data Export</h1>
            <div style="color:#64748b;font-size:10px;margin-top:2px;">
                Universities &amp; Boards · Courses · Fee Structures
            </div>
        </div>
        <div class="meta">
            <div><b>Generated:</b> {{ $generatedAt->format('d M Y, h:i A') }}</div>
            <div><b>By:</b> {{ $generatedBy->name }} ({{ ucfirst($generatedBy->role ?? 'user') }})</div>
            <div><b>Universities / Boards:</b> {{ $universities->count() }}</div>
            <div><b>Courses:</b> {{ $universities->sum(fn ($u) => $u->courses->count()) }}</div>
            <div><b>Fee Structures:</b> {{ $fees->count() }}</div>
        </div>
    </div>

    {{-- ──── Summary tiles ──── --}}
    @php
        $uniCount   = $universities->where('type', \App\Models\University::TYPE_UNIVERSITY)->count();
        $boardCount = $universities->where('type', \App\Models\University::TYPE_BOARD)->count();
        $allCourses = $universities->flatMap->courses;
        $lateralCount = $allCourses->where('lateral_entry', true)->count();
        $totalAnnualSemFees = $allCourses->sum(fn ($c) => (float) $c->fee_per_sem);
        $totalRegFees       = $allCourses->sum(fn ($c) => (float) $c->registration_fee);
    @endphp
    <div class="summary">
        <div class="stat"><span class="v">{{ $uniCount }}</span><span class="l">Universities</span></div>
        <div class="stat"><span class="v">{{ $boardCount }}</span><span class="l">Boards</span></div>
        <div class="stat"><span class="v">{{ $allCourses->count() }}</span><span class="l">Courses</span></div>
        <div class="stat"><span class="v">{{ $lateralCount }}</span><span class="l">Lateral Entry</span></div>
        <div class="stat"><span class="v">{{ $fees->count() }}</span><span class="l">Fee Records</span></div>
        <div class="stat"><span class="v">₹{{ number_format($totalRegFees + $totalAnnualSemFees) }}</span><span class="l">Reg + Period Fees</span></div>
    </div>

    {{-- ──── 1. UNIVERSITIES & BOARDS ──── --}}
    <div class="section">
        <h2 class="section-title">1. Universities &amp; Boards</h2>
        <div class="section-sub">Every university / board entry with its full profile.</div>

        @if ($universities->isEmpty())
            <div class="empty">No universities or boards added yet.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:36px;" class="center">#</th>
                        <th>Name</th>
                        <th class="center">Type</th>
                        <th>Address</th>
                        <th>Website</th>
                        <th class="num">Reg. Fee (₹)</th>
                        <th class="num">Courses</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($universities as $i => $u)
                        <tr>
                            <td class="center">{{ $i + 1 }}</td>
                            <td><b>{{ $u->name }}</b></td>
                            <td class="center">
                                @if ($u->type === \App\Models\University::TYPE_BOARD)
                                    <span class="badge badge-board">Board</span>
                                @else
                                    <span class="badge badge-uni">University</span>
                                @endif
                            </td>
                            <td>{{ $u->address ?: '—' }}</td>
                            <td>{{ $u->website ?: '—' }}</td>
                            <td class="num">{{ number_format((float) $u->registration_fee, 2) }}</td>
                            <td class="num">{{ $u->courses->count() }}</td>
                            <td>{{ $u->created_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- ──── 2. COURSES ──── --}}
    <div class="section">
        <h2 class="section-title">2. Courses</h2>
        <div class="section-sub">Every course offered by each university / board, with mode, duration, fees and live enrolment.</div>

        @if ($allCourses->isEmpty())
            <div class="empty">No courses defined.</div>
        @else
            @foreach ($universities as $u)
                @if ($u->courses->isNotEmpty())
                    <div class="uni-block">
                        <div class="uni-header">
                            <div>
                                <h3>{{ $u->name }}
                                    @if ($u->type === \App\Models\University::TYPE_BOARD)
                                        <span class="badge badge-board" style="margin-left:6px;">Board</span>
                                    @else
                                        <span class="badge badge-uni" style="margin-left:6px;">University</span>
                                    @endif
                                </h3>
                                <div style="color:#64748b;font-size:10px;margin-top:1px;">
                                    {{ $u->courses->count() }} course{{ $u->courses->count() === 1 ? '' : 's' }} ·
                                    Registration fee for this board / uni: ₹{{ number_format((float) $u->registration_fee, 2) }}
                                </div>
                            </div>
                            <div class="uni-meta">
                                @if ($u->website)<div>{{ $u->website }}</div>@endif
                                @if ($u->address)<div style="max-width:300px;">{{ $u->address }}</div>@endif
                            </div>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th style="width:30px;" class="center">#</th>
                                    <th>Course</th>
                                    <th>Mode</th>
                                    <th class="num">Duration</th>
                                    <th class="num">Periods</th>
                                    <th>Period&nbsp;Type</th>
                                    <th class="num">Reg.&nbsp;Fee</th>
                                    <th class="num">Fee&nbsp;/&nbsp;Period</th>
                                    <th class="num">Total Fee</th>
                                    <th class="center">Lateral</th>
                                    <th class="center">Current</th>
                                    <th class="num">Students</th>
                                    <th>Subjects</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($u->courses as $i => $c)
                                    @php
                                        $isBoard = $c->isBoard();
                                        $periodLabel = $isBoard ? 'Annual' : 'Semester';
                                    @endphp
                                    <tr>
                                        <td class="center">{{ $i + 1 }}</td>
                                        <td><b>{{ $c->name }}</b></td>
                                        <td>{{ $c->mode ?: '—' }}</td>
                                        <td class="num">{{ rtrim(rtrim(number_format((float) $c->duration_years, 1), '0'), '.') }} yrs</td>
                                        <td class="num">{{ $c->feePeriodCount() }}</td>
                                        <td>{{ $periodLabel }}</td>
                                        <td class="num">{{ number_format((float) $c->registration_fee, 2) }}</td>
                                        <td class="num">{{ number_format((float) $c->fee_per_sem, 2) }}</td>
                                        <td class="num"><b>{{ number_format($c->totalFee(), 2) }}</b></td>
                                        <td class="center">
                                            @if ($c->lateral_entry)
                                                <span class="badge badge-yes">Yes</span>
                                            @else
                                                <span class="badge badge-no">No</span>
                                            @endif
                                        </td>
                                        <td class="center">{{ $c->currentPeriodLabel() }}</td>
                                        <td class="num">{{ (int) ($studentCounts[$c->id] ?? 0) }}</td>
                                        <td style="font-size:9px;color:#475569;max-width:160px;">{{ $c->subjects ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endforeach
        @endif
    </div>

    {{-- ──── 3. FEE STRUCTURES ──── --}}
    <div class="section">
        <h2 class="section-title">3. Fee Structures</h2>
        <div class="section-sub">Auto-synced records from the course fees — what each enrolled cohort owes per period and in total.</div>

        @if ($fees->isEmpty())
            <div class="empty">No fee structures recorded.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:30px;" class="center">#</th>
                        <th>University / Board</th>
                        <th>Course</th>
                        <th class="num">Duration</th>
                        <th class="num">Periods</th>
                        <th>Period Type</th>
                        <th class="num">Reg. Fee</th>
                        <th class="num">Fee / Period</th>
                        <th class="num">Total Fee</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($fees as $i => $f)
                        @php
                            $course = $f->course;
                            $isBoard = $course?->isBoard() ?? false;
                            $periods = $course?->feePeriodCount() ?? 0;
                            $perFee  = (float) ($course?->fee_per_sem ?? $f->fee_per_sem);
                            $regFee  = (float) ($course?->registration_fee ?? 0);
                            $total   = $course?->totalFee() ?? ($perFee * $periods + $regFee);
                        @endphp
                        <tr>
                            <td class="center">{{ $i + 1 }}</td>
                            <td>{{ $f->university?->name ?? '—' }}
                                @if ($f->university)
                                    @if ($f->university->type === \App\Models\University::TYPE_BOARD)
                                        <span class="badge badge-board" style="margin-left:4px;">Board</span>
                                    @else
                                        <span class="badge badge-uni" style="margin-left:4px;">University</span>
                                    @endif
                                @endif
                            </td>
                            <td><b>{{ $course?->name ?? '—' }}</b></td>
                            <td class="num">{{ $course ? rtrim(rtrim(number_format((float) $course->duration_years, 1), '0'), '.').' yrs' : '—' }}</td>
                            <td class="num">{{ $periods }}</td>
                            <td>{{ $isBoard ? 'Annual' : 'Semester' }}</td>
                            <td class="num">{{ number_format($regFee, 2) }}</td>
                            <td class="num">{{ number_format($perFee, 2) }}</td>
                            <td class="num"><b>{{ number_format($total, 2) }}</b></td>
                            <td>{{ $f->created_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="doc-foot">
        End of report · Generated by SSB Education portal · {{ $generatedAt->format('d M Y H:i:s') }}
    </div>
</div>

<script>
    // Fire the print dialog right after the page paints so the user
    // lands in their browser's "Save as PDF" view immediately. Wrapped
    // in setTimeout so fonts/layout are settled first.
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 350);
    });
</script>

</body>
</html>
