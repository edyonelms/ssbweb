{{--
    University-letterhead admission form template.

    Two-page A4 layout (numbered fields on page 1, academic records +
    declaration + office-use section on page 2). The university name,
    address, logo and accreditation badge are pulled from the
    University master-data record so the same template renders the
    right brand for every university — upload the official letterhead
    assets via Master Data → University → Logo / Accreditation Badge.

    Auto-fires the browser print dialog on load so the user lands
    directly in "Save as PDF".
--}}
@php
    /** @var \App\Models\Student $student */
    $university = $student->university;
    $course     = $student->course;

    // Pretty session string (e.g. "JAN-2026"). Falls back to the
    // student's created month, then to today.
    $sessionDate = $student->created_at ?? now();
    $session = strtoupper($sessionDate->format('M-Y'));

    // Sex / category checkbox state. Both columns degrade gracefully
    // when the student record has the value blank.
    $genderRaw = strtolower((string) $student->gender);
    $isMale    = in_array($genderRaw, ['m', 'male'], true);
    $isFemale  = in_array($genderRaw, ['f', 'female'], true);

    $catRaw = strtolower((string) $student->category);
    $catMap = [
        'gen'    => $catRaw === 'general' || $catRaw === 'gen',
        'obc'    => $catRaw === 'obc',
        'sc'     => $catRaw === 'sc',
        'st'     => $catRaw === 'st',
        'others' => $catRaw !== '' && ! in_array($catRaw, ['general', 'gen', 'obc', 'sc', 'st'], true),
    ];

    // Pin code → fixed 6 boxes. Pad / trim to keep the grid aligned
    // when the stored value is shorter or longer than expected.
    $pinDigits = preg_replace('/\D/', '', (string) $student->pincode);
    $pinDigits = str_pad(substr($pinDigits, 0, 6), 6, ' ', STR_PAD_RIGHT);

    // DOB split into d / m / y boxes.
    $dob = $student->dob ? \Carbon\Carbon::parse($student->dob) : null;

    // Academic records — normalise by level so the table renders one
    // row per X / XII / UG / PG / Other regardless of stored order.
    $rawRecords  = collect($student->academic_records ?? []);
    $levelLookup = function (string $level) use ($rawRecords) {
        return $rawRecords->first(function ($r) use ($level) {
            return strtolower((string) ($r['level'] ?? '')) === strtolower($level);
        }) ?? [];
    };
    $rowX     = $levelLookup('X')   ?: $levelLookup('10th');
    $rowXII   = $levelLookup('XII') ?: $levelLookup('12th') ?: $levelLookup('Intermediate');
    $rowUG    = $levelLookup('UG')  ?: $levelLookup('Graduation') ?: $levelLookup('Bachelor');
    $rowPG    = $levelLookup('PG')  ?: $levelLookup('Post Graduation') ?: $levelLookup('Master');
    $rowOther = $levelLookup('Other') ?: $levelLookup('Others');

    $val = fn ($v) => filled($v) ? e($v) : '';
@endphp
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admission Form — {{ $student->name }}</title>
<style>
    @page { size: A4; margin: 8mm 6mm; }
    * { box-sizing: border-box; }
    html, body {
        margin: 0; padding: 0;
        font-family: 'Times New Roman', Times, serif;
        color: #000;
        font-size: 11px;
        line-height: 1.35;
        background: #f3f4f6;
    }
    .sheet {
        width: 210mm;
        min-height: 297mm;
        margin: 8px auto;
        background: #fff;
        padding: 10mm 9mm 8mm;
        position: relative;
    }
    .sheet + .sheet { page-break-before: always; }

    /* ─── Letterhead row ─── */
    .head-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }
    .app-meta {
        display: flex;
        gap: 30px;
        align-items: center;
        font-weight: 700;
        font-size: 11px;
    }
    .app-meta .label { display: inline-block; min-width: 80px; }
    .app-meta .box {
        display: inline-block;
        border: 1px solid #000;
        min-width: 90px;
        height: 18px;
        padding: 1px 6px;
        font-weight: 400;
        text-align: center;
        line-height: 16px;
    }
    .photo-box {
        width: 30mm; height: 38mm;
        border: 1px solid #000;
        overflow: hidden;
        display: flex; align-items: center; justify-content: center;
        font-size: 10px; color: #999;
        background: #fff;
    }
    .photo-box img { width: 100%; height: 100%; object-fit: cover; }

    /* ─── University brand band ─── */
    .brand {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 4px 0 2px;
    }
    .brand .lh {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }
    .brand .logo {
        width: 22mm; height: 22mm;
        display: flex; align-items: center; justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }
    .brand .logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .brand .logo .ph {
        width: 100%; height: 100%;
        border: 1px dashed #999;
        color: #999;
        display: flex; align-items: center; justify-content: center;
        font-size: 9px;
        text-align: center;
        padding: 4px;
    }
    .brand .name {
        flex: 1;
        text-align: center;
        line-height: 1.1;
    }
    .brand .name .uni {
        font-family: 'Georgia', 'Times New Roman', serif;
        font-size: 26px;
        font-weight: 700;
        color: #1d4ed8;
        letter-spacing: 3px;
    }
    .brand .name .addr {
        font-family: 'Georgia', serif;
        font-size: 11px;
        color: #334155;
        margin-top: 3px;
        line-height: 1.3;
    }
    .brand .name .tagline {
        font-family: 'Georgia', serif;
        font-style: italic;
        color: #c2410c;
        font-size: 12px;
        font-weight: 700;
        margin-top: 2px;
    }
    .brand .badge {
        width: 22mm; height: 22mm;
        display: flex; align-items: center; justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }
    .brand .badge img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .brand .badge .ph {
        width: 100%; height: 100%;
        border: 1px dashed #999;
        color: #999;
        display: flex; align-items: center; justify-content: center;
        font-size: 9px;
        text-align: center;
        padding: 4px;
    }
    /* Right-side spacer keeps the centred name visually balanced even when
       no badge sits on the right. Matches combined logo + badge width. */
    .brand .balancer { width: 22mm; flex-shrink: 0; }
    .brand .balancer.with-badge { width: 50mm; }

    /* ─── Title ─── */
    .form-title {
        text-align: center;
        margin: 6px 0 4px;
    }
    .form-title h1 {
        font-size: 13px;
        font-weight: 700;
        text-decoration: underline;
        margin: 0;
        letter-spacing: 0.5px;
    }
    .form-title .instr {
        font-size: 10.5px;
        margin-top: 2px;
        font-style: italic;
    }

    /* ─── Signature box row ─── */
    .sig-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 6px;
    }
    .sig-row .label { font-weight: 700; font-size: 11px; }
    .sig-row .sig-box {
        width: 40mm; height: 11mm;
        border: 1px solid #000;
        overflow: hidden;
        display: flex; align-items: center; justify-content: center;
    }
    .sig-row .sig-box img { max-width: 100%; max-height: 100%; object-fit: contain; }

    /* ─── Numbered fields ─── */
    .row {
        display: flex;
        align-items: stretch;
        margin-top: 5px;
        gap: 8px;
    }
    .row .stack { display: flex; flex-direction: column; gap: 2px; flex: 1; }
    .lbl {
        font-weight: 700;
        font-size: 11px;
    }
    .lbl .light { font-weight: 400; font-style: italic; font-size: 10px; }
    .full-box {
        border: 1px solid #000;
        padding: 3px 6px;
        min-height: 22px;
        line-height: 16px;
        font-size: 11.5px;
        background: #fff;
    }
    .multi-box {
        border: 1px solid #000;
        padding: 3px 6px;
        min-height: 44px;
        white-space: pre-wrap;
        font-size: 11.5px;
        line-height: 1.35;
    }
    .inline-row {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 6px;
        font-size: 11px;
        font-weight: 700;
    }
    .checkbox {
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .checkbox .cb {
        width: 12px; height: 12px;
        border: 1px solid #000;
        display: inline-block;
        text-align: center;
        line-height: 10px;
        font-size: 10px;
        background: #fff;
    }
    .checkbox .cb.checked {
        background: #2563eb;
        color: #fff;
        font-weight: 700;
    }
    .checkbox .cb.checked::after { content: '\2713'; }
    .digit-grid {
        display: inline-flex;
        gap: 2px;
        margin-left: 4px;
    }
    .digit-grid span {
        width: 18px; height: 18px;
        border: 1px solid #000;
        text-align: center;
        font-weight: 700;
        font-size: 12px;
        line-height: 16px;
        background: #fff;
    }
    .three-col {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 6px;
        margin-top: 4px;
    }
    .small-lbl {
        font-size: 10.5px;
        color: #000;
    }

    /* ─── Fee details block ─── */
    .fee-block {
        margin-top: 8px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        column-gap: 12px;
        row-gap: 4px;
    }
    .fee-block .field {
        display: flex;
        align-items: baseline;
        gap: 6px;
    }
    .fee-block .field .k { font-weight: 700; min-width: 110px; }
    .fee-block .field .v {
        flex: 1;
        border-bottom: 1px solid #000;
        min-height: 16px;
        padding: 0 4px;
    }
    .fee-words {
        grid-column: 1 / span 2;
        display: flex; gap: 6px; align-items: baseline;
    }
    .fee-words .k { font-weight: 700; min-width: 110px; }
    .fee-words .v {
        flex: 1;
        border-bottom: 1px solid #000;
        min-height: 16px;
        padding: 0 4px;
    }

    .nat-cat {
        display: grid;
        grid-template-columns: 1fr 1.4fr;
        gap: 14px;
        margin-top: 8px;
    }
    .nat-cat .label-line {
        font-weight: 700;
    }
    .nat-cat .label-line .light {
        font-weight: 400; font-style: italic; font-size: 10px;
    }
    .cat-chips {
        margin-top: 4px;
        display: flex;
        gap: 16px;
    }

    /* ─── Page 2 — academic records table ─── */
    table.acad {
        width: 100%;
        border-collapse: collapse;
        margin-top: 4px;
        font-size: 11px;
    }
    table.acad th, table.acad td {
        border: 1px solid #000;
        padding: 4px 5px;
        vertical-align: top;
        text-align: left;
    }
    table.acad th {
        background: #f1f5f9;
        font-weight: 700;
        text-align: center;
    }
    table.acad td.center { text-align: center; }

    /* ─── Declaration & signatures ─── */
    .declaration-title {
        text-align: center;
        font-weight: 700;
        margin: 14px 0 6px;
        text-decoration: underline;
        font-size: 12px;
    }
    .declaration p {
        margin: 0 0 8px;
        text-align: justify;
        line-height: 1.45;
    }
    .sign-line-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-top: 18px;
    }
    .sign-line {
        border-top: 1px solid #000;
        padding-top: 2px;
        text-align: center;
        font-size: 11px;
    }
    .sign-line.long {
        display: flex;
        align-items: flex-end;
        gap: 8px;
    }
    .sign-line.long .line {
        flex: 1;
        border-bottom: 1px solid #000;
        height: 18px;
    }
    .sign-line.long .lbl-after { font-weight: 700; }

    /* ─── Office-use boxes ─── */
    .office-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
        margin-top: 14px;
    }
    .office-card {
        border: 1px solid #000;
        padding: 4px 6px;
        font-size: 11px;
    }
    .office-card .row1 {
        display: flex; justify-content: space-between; align-items: center;
        font-weight: 700;
    }
    .office-card .yn {
        display: inline-flex; gap: 4px; align-items: center;
    }
    .office-card .yn .pair { display: inline-flex; gap: 2px; align-items: center; }
    .office-card .yn .cell {
        width: 18px; height: 18px;
        border: 1px solid #000;
        display: inline-block;
    }
    .office-card .yn .ynlbl {
        font-size: 10px;
    }

    .office-note {
        margin-top: 8px;
        font-size: 11px;
    }

    .sep {
        text-align: center;
        margin: 12px 0 6px;
        letter-spacing: 1px;
    }
    .office-title {
        text-align: center;
        font-weight: 700;
        text-decoration: underline;
        font-size: 11.5px;
    }
    .checklist {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        gap: 12px;
        margin-top: 6px;
    }
    .checklist ul {
        margin: 0; padding: 0; list-style: none;
    }
    .checklist ul li {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 3px;
        font-size: 10.5px;
    }
    .checklist ul li .cb {
        width: 11px; height: 11px;
        border: 1px solid #000;
        display: inline-block;
        flex-shrink: 0;
    }
    .eligible-box {
        border: 1px solid #000;
        padding: 4px 6px;
        font-size: 10.5px;
    }
    .eligible-box .head {
        font-weight: 700;
        text-align: center;
        margin-bottom: 4px;
    }
    .eligible-box .er {
        display: flex; align-items: center; gap: 6px;
        margin-bottom: 3px;
    }
    .eligible-box .er .label { min-width: 40px; }
    .eligible-box .er .pair { display: inline-flex; gap: 4px; align-items: center; }
    .eligible-box .er .cell { width: 14px; height: 14px; border: 1px solid #000; display: inline-block; }

    .approver {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 30px;
        margin-top: 14px;
        font-size: 11px;
    }
    .approver .dotted {
        border-bottom: 1px dotted #000;
        min-height: 14px;
        padding: 0 2px;
    }
    .approver .stamp {
        text-align: center;
        margin-top: 24px;
        font-weight: 700;
    }

    /* ─── Floating toolbar ─── */
    .toolbar {
        position: fixed; top: 10px; right: 10px;
        display: flex; gap: 8px; z-index: 100;
    }
    .toolbar button, .toolbar a {
        background: #db2777; color: #fff; border: 0;
        padding: 8px 14px; border-radius: 6px;
        font-size: 12px; font-weight: 700; cursor: pointer;
        text-decoration: none;
        box-shadow: 0 4px 14px rgba(0,0,0,0.18);
        font-family: 'Segoe UI', Arial, sans-serif;
    }
    .toolbar a.secondary {
        background: #fff; color: #334155; border: 1px solid #cbd5e1;
    }
    @media print {
        html, body { background: #fff; }
        .toolbar { display: none !important; }
        .sheet { margin: 0; padding: 8mm 7mm; box-shadow: none; }
    }
</style>
</head>
<body>

<div class="toolbar">
    <button type="button" onclick="window.print()">Save / Print PDF</button>
    <a href="{{ route('students.index') }}" class="secondary">Close</a>
</div>

{{-- ═══════════════ PAGE 1 ═══════════════ --}}
<div class="sheet">

    {{-- ── Application No / Session / Photo row ── --}}
    <div class="head-row">
        <div class="app-meta">
            <div>
                <span class="label">Application No.</span>
                <span class="box">{{ $student->admission_no ?: $student->id }}</span>
            </div>
            <div>
                <span class="label">Session</span>
                <span class="box">{{ $session }}</span>
            </div>
        </div>
        <div class="photo-box">
            @if ($docUrls['photo_path'] ?? null)
                <img src="{{ $docUrls['photo_path'] }}" alt="Photo">
            @else
                Photo
            @endif
        </div>
    </div>

    {{-- ── University / board brand band — logo (+ accreditation badge
         on the left for universities only) + name + address + website. --}}
    @php
        $isUniType  = $university?->type === \App\Models\University::TYPE_UNIVERSITY;
        $showBadge  = $isUniType && $university?->naac_image_url;
        $brandName  = strtoupper($university?->name ?? 'University / Board');
    @endphp
    <div class="brand">
        <div class="lh">
            <div class="logo">
                @if ($university?->image_url)
                    <img src="{{ $university->image_url }}" alt="{{ $university->name }} logo">
                @else
                    <div class="ph">Upload logo via Master Data</div>
                @endif
            </div>
            @if ($showBadge)
                <div class="badge">
                    <img src="{{ $university->naac_image_url }}" alt="Accreditation badge">
                </div>
            @endif
        </div>
        <div class="name">
            <div class="uni">{{ $brandName }}</div>
            @if ($university?->address)
                <div class="addr">{{ $university->address }}</div>
            @endif
            @if ($university?->website)
                <div class="tagline">{{ $university->website }}</div>
            @endif
        </div>
        <div class="balancer {{ $showBadge ? 'with-badge' : '' }}"></div>
    </div>

    <div class="form-title">
        <h1>APPLICATION FORM FOR ADMISSION</h1>
        <div class="instr">To be filled by the candidate in CAPITAL LETTERS in English</div>
    </div>

    {{-- ── Specimen signature row ── --}}
    <div class="sig-row">
        <div class="label">Specimen Signature of the Candidate (Inside the Box)</div>
        <div class="sig-box">
            @if ($docUrls['student_sign_path'] ?? null)
                <img src="{{ $docUrls['student_sign_path'] }}" alt="Signature">
            @endif
        </div>
    </div>

    {{-- ── Enrollment + Programme ── --}}
    <div class="row" style="margin-top: 8px;">
        <div class="stack" style="flex: 0 0 32%;">
            <div class="lbl">ENROLLMENT NUMBER<br><span class="light">(For Office Use Only)</span></div>
            <div class="full-box">&nbsp;</div>
        </div>
        <div class="stack" style="flex: 1;">
            <div class="lbl">PROGRAMME APPLIED FOR<br><span class="light">(Including Subject / Specialization)</span></div>
            <div class="full-box">{{ strtoupper($val($course?->name)) }}</div>
        </div>
    </div>

    {{-- ── 1. Name ── --}}
    <div class="row">
        <div class="stack">
            <div class="lbl">1. Name in CAPITAL LETTERS (In English)</div>
            <div class="full-box">{{ strtoupper($val($student->name)) }}</div>
        </div>
    </div>

    {{-- ── 2. Father's Name ── --}}
    <div class="row">
        <div class="stack">
            <div class="lbl">2. Father's Name <span class="light">(All candidates, including married women, must write the father's name)</span></div>
            <div class="full-box">{{ strtoupper($val($student->father_name ?: $student->parent_name)) }}</div>
        </div>
    </div>

    {{-- ── 3. Mother's Name ── --}}
    <div class="row">
        <div class="stack">
            <div class="lbl">3. Mother's Name</div>
            <div class="full-box">{{ strtoupper($val($student->mother_name)) }}</div>
        </div>
    </div>

    {{-- ── 4. Sex / 5. DOB ── --}}
    <div class="inline-row">
        <span>4. Sex:</span>
        <span class="checkbox"><span class="cb {{ $isMale ? 'checked' : '' }}"></span> Male</span>
        <span class="checkbox"><span class="cb {{ $isFemale ? 'checked' : '' }}"></span> Female</span>
        <span style="margin-left: auto;">5. Date of Birth:</span>
        <span class="digit-grid">
            <span>{{ $dob?->format('d')[0] ?? '' }}</span>
            <span>{{ $dob?->format('d')[1] ?? '' }}</span>
        </span>
        <span class="digit-grid">
            <span>{{ $dob?->format('m')[0] ?? '' }}</span>
            <span>{{ $dob?->format('m')[1] ?? '' }}</span>
        </span>
        <span class="digit-grid">
            <span>{{ $dob?->format('Y')[0] ?? '' }}</span>
            <span>{{ $dob?->format('Y')[1] ?? '' }}</span>
            <span>{{ $dob?->format('Y')[2] ?? '' }}</span>
            <span>{{ $dob?->format('Y')[3] ?? '' }}</span>
        </span>
    </div>

    {{-- ── 6. Address ── --}}
    <div class="row">
        <div class="stack">
            <div class="lbl">6. Address for Correspondence <span class="light">(Do not repeat name)</span></div>
            @php
                $addrLines = array_filter([
                    $student->address,
                    trim(($student->city ?: '').(($student->city && $student->state) ? ' , ' : '').($student->state ?: '')),
                    $student->country ?: null,
                ]);
            @endphp
            <div class="multi-box">{{ strtoupper(implode("\n", $addrLines)) }}</div>
        </div>
    </div>

    {{-- Pin Code row --}}
    <div class="inline-row">
        <span style="margin-left:auto;">Pin Code</span>
        <span class="digit-grid">
            @for ($i = 0; $i < 6; $i++)
                <span>{{ $pinDigits[$i] !== ' ' ? $pinDigits[$i] : '' }}</span>
            @endfor
        </span>
    </div>

    {{-- Phone / Mobile / Email --}}
    <div class="three-col">
        <div class="stack">
            <div class="small-lbl">Phone No. with STD Code</div>
            <div class="full-box">&nbsp;</div>
        </div>
        <div class="stack">
            <div class="small-lbl">Mobile No.</div>
            <div class="full-box">{{ $val($student->mobile) }}</div>
        </div>
        <div class="stack">
            <div class="small-lbl">E-mail</div>
            <div class="full-box">{{ $val($student->email) }}</div>
        </div>
    </div>

    {{-- 7. Fee payment block --}}
    <div style="margin-top: 8px; font-size: 10.5px; line-height: 1.35;">
        Please ensure the prescribed fee draft and supporting certificates are enclosed as required.<br>
        <b>7. Details of Fee Payment:</b> Demand Draft drawn in favour of the University, payable at the registered campus location.
    </div>

    <div class="fee-block">
        <div class="field"><span class="k">Demand Draft No.</span><span class="v">&nbsp;</span></div>
        <div class="field"><span class="k">Date</span><span class="v">&nbsp;</span></div>
        <div class="field"><span class="k">Bank</span><span class="v">&nbsp;</span></div>
        <div class="field"><span class="k">Amount</span><span class="v">&nbsp;</span></div>
        <div class="fee-words"><span class="k">Amount in Words</span><span class="v">&nbsp;</span></div>
    </div>

    {{-- 8 / 9 / 10 — Nationality, Category, Employment --}}
    <div class="nat-cat">
        <div>
            <div class="label-line">8. Nationality</div>
            <div class="full-box" style="margin-top:2px;">{{ strtoupper($val($student->nationality)) }}</div>
            <div class="label-line" style="margin-top:8px;">10. Employment Status</div>
            <div class="full-box" style="margin-top:2px;">&nbsp;</div>
        </div>
        <div>
            <div class="label-line">9. Category <span class="light">(tick mark whichever is applicable)</span></div>
            <div class="small-lbl">(Please attach category certificate if applicable)</div>
            <div class="cat-chips" style="margin-top:14px;">
                <span class="checkbox"><span class="cb {{ $catMap['gen']    ? 'checked' : '' }}"></span> Gen.</span>
                <span class="checkbox"><span class="cb {{ $catMap['obc']    ? 'checked' : '' }}"></span> OBC</span>
                <span class="checkbox"><span class="cb {{ $catMap['sc']     ? 'checked' : '' }}"></span> SC</span>
                <span class="checkbox"><span class="cb {{ $catMap['st']     ? 'checked' : '' }}"></span> ST</span>
                <span class="checkbox"><span class="cb {{ $catMap['others'] ? 'checked' : '' }}"></span> Others</span>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════ PAGE 2 ═══════════════ --}}
<div class="sheet">

    {{-- ── Academic records table ── --}}
    <table class="acad">
        <thead>
            <tr>
                <th style="width:9%;">Name of<br>Examination</th>
                <th>Subject</th>
                <th style="width:9%;">Year of<br>Passing</th>
                <th style="width:18%;">Name of<br>University / Board</th>
                <th style="width:14%;">Division / Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach ([
                ['X',     $rowX],
                ['XII',   $rowXII],
                ['UG',    $rowUG],
                ['PG',    $rowPG],
                ['OTHER', $rowOther],
            ] as [$label, $row])
                <tr>
                    <td><b>{{ $label }}</b></td>
                    <td>{{ strtoupper($val($row['subject'] ?? '')) }}</td>
                    <td class="center">{{ $val($row['year'] ?? '') }}</td>
                    <td>{{ strtoupper($val($row['board'] ?? '')) }}</td>
                    <td>{{ strtoupper($val($row['division'] ?? $row['grade'] ?? '')) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Declaration ── --}}
    <div class="declaration-title">DECLARATION</div>
    <div class="declaration">
        <p>
            I solemnly affirm that all the particulars stated by me in this application are true and correct
            to the best of my knowledge and belief. I further confirm that the attested photocopies of the
            certificates submitted with this application are true copies of the originals and I have read and
            understood the prospectus along with the rules and regulations of the University. Should any
            information furnished by me later be found to be incorrect at any stage, I agree to forfeit the
            fee deposited as well as any claim to admission.
        </p>
    </div>

    {{-- Place & Signature ── --}}
    <div class="sign-line-row">
        <div class="sign-line long">
            <span class="lbl-after" style="font-weight:700;">Place &amp; Date :</span>
            <span class="line"></span>
        </div>
        <div class="sign-line long">
            <span class="line"></span>
            <span class="lbl-after">Signature of the Applicant</span>
        </div>
    </div>

    {{-- ── Office-use boxes ── --}}
    <div class="office-grid">
        <div class="office-card">
            <div class="row1">
                <span>Eligible:</span>
                <span class="yn">
                    <span class="pair"><span class="ynlbl">Yes</span><span class="cell"></span></span>
                    <span class="pair"><span class="ynlbl">No</span><span class="cell"></span></span>
                </span>
            </div>
        </div>
        <div class="office-card">
            <div class="row1">
                <span>Course Fee paid in Full:</span>
                <span class="yn">
                    <span class="pair"><span class="ynlbl">Yes</span><span class="cell"></span></span>
                    <span class="pair"><span class="ynlbl">No</span><span class="cell"></span></span>
                </span>
            </div>
        </div>
        <div class="office-card">
            <div class="row1">
                <span>Receipt Issued:</span>
                <span class="yn">
                    <span class="pair"><span class="ynlbl">Yes</span><span class="cell"></span></span>
                    <span class="pair"><span class="ynlbl">No</span><span class="cell"></span></span>
                </span>
            </div>
        </div>
        <div class="office-card">
            <div class="row1">
                <span>Originals Verified:</span>
                <span class="yn">
                    <span class="pair"><span class="ynlbl">Yes</span><span class="cell"></span></span>
                    <span class="pair"><span class="ynlbl">No</span><span class="cell"></span></span>
                </span>
            </div>
        </div>
    </div>

    <div class="office-note">Granted provisional admission subject to ratification by the University.</div>

    <div class="sep">--------------------------------------------------------------------------------------------------------------------</div>
    <div class="office-title">(To be filled by the Office)</div>

    {{-- Document checklist + Eligible-for-course --}}
    <div class="checklist">
        <ul>
            <li><span class="cb"></span> Photocopy of High School Marksheet &amp; Certificate</li>
            <li><span class="cb"></span> Photocopy of Intermediate Marksheet &amp; Certificate</li>
            <li><span class="cb"></span> Photocopy of Graduation I, II, III Year Marksheet &amp; Certificate <span style="font-style:italic;">(only for PG courses)</span></li>
            <li><span class="cb"></span> Photocopy of previous-year marksheet <span style="font-style:italic;">(for credit transfer cases)</span></li>
            <li><span class="cb"></span> Photocopy of required Degree / Diploma <span style="font-style:italic;">(for lateral entry)</span></li>
        </ul>
        <div class="eligible-box">
            <div class="head">Eligible for the Course:</div>
            <div class="er">
                <span class="label">1. U.G.</span>
                <span class="pair"><span class="cell"></span><span>Yes</span></span>
                <span class="pair"><span class="cell"></span><span>No</span></span>
            </div>
            <div class="er">
                <span class="label">2. P.G.</span>
                <span class="pair"><span class="cell"></span><span>Yes</span></span>
                <span class="pair"><span class="cell"></span><span>No</span></span>
            </div>
            <div class="er">
                <span class="label">3. C.T.</span>
                <span class="pair"><span class="cell"></span><span>Yes</span></span>
                <span class="pair"><span class="cell"></span><span>No</span></span>
            </div>
            <div class="er">
                <span class="label">4. L.E.</span>
                <span class="pair"><span class="cell"></span><span>Yes</span></span>
                <span class="pair"><span class="cell"></span><span>No</span></span>
            </div>
        </div>
    </div>

    {{-- Approver block --}}
    <div class="approver">
        <div>
            <div style="display:flex;align-items:baseline;gap:6px;">
                <span style="font-weight:700;">Recommendation of Checking Officer</span>
                <span class="dotted" style="flex:1;"></span>
            </div>
            <div style="margin-top:6px;">
                This is to certify that the candidate is eligible for admission. Enrollment No. may be allotted.
            </div>
            <div style="margin-top:8px;display:flex;align-items:baseline;gap:6px;">
                <span style="font-weight:700;">Enrollment No.</span>
                <span class="dotted" style="flex:1;"></span>
            </div>
            <div style="margin-top:6px;display:flex;align-items:baseline;gap:6px;">
                <span style="font-weight:700;">Checked By</span>
                <span class="dotted" style="flex:1;"></span>
            </div>
            <div style="margin-top:6px;display:flex;align-items:baseline;gap:6px;">
                <span style="font-weight:700;">Date</span>
                <span class="dotted" style="flex:1;"></span>
            </div>
        </div>
        <div class="stamp">
            <div class="dotted" style="border-bottom:1px dotted #000;min-height:30px;"></div>
            <div style="margin-top:4px;">Signature</div>
            <div style="font-style:italic;">(Sanctioning Authority)</div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 400);
    });
</script>

</body>
</html>
