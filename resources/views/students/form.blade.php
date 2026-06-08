{{--
    Standalone admission-form template.

    Rendered by StudentsController::form(); served either as a normal
    page (the registrar clicks "View Form" in the listing and a new tab
    opens) or as a downloadable .html file when ?download=1 is set.

    Everything is inlined — fonts, styles, photo + sign — so the file
    is portable: drop it onto a thumb drive and it still renders the
    same on any browser.
--}}
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admission Form — {{ $student->name }}</title>
<style>
    :root { color-scheme: light; }
    * { box-sizing: border-box; }
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        margin: 0; padding: 24px; background: #f8fafc; color: #0f172a; font-size: 12px; line-height: 1.45;
    }
    .sheet { max-width: 900px; margin: 0 auto; background: white; padding: 36px 40px; border: 1px solid #e2e8f0; border-radius: 14px; }
    .topbar { display: flex; align-items: center; gap: 16px; padding-bottom: 16px; border-bottom: 2px solid #ec4899; margin-bottom: 22px; }
    .topbar .brand { font-weight: 800; font-size: 18px; letter-spacing: 0.5px; }
    .topbar .sub { color: #64748b; font-size: 11px; margin-top: 2px; }
    .topbar .meta { margin-left: auto; text-align: right; font-size: 11px; color: #475569; }
    .topbar .photo { width: 88px; height: 110px; border: 1px solid #e2e8f0; border-radius: 6px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8; overflow: hidden; flex-shrink: 0; }
    .topbar .photo img { width: 100%; height: 100%; object-fit: cover; }
    h1 { font-size: 18px; margin: 0; font-weight: 800; color: #1e293b; }
    h2 { font-size: 12px; margin: 18px 0 8px; font-weight: 700; color: #be185d; text-transform: uppercase; letter-spacing: 0.6px; border-bottom: 1px solid #f1f5f9; padding-bottom: 4px; }
    .grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px 18px; }
    .grid.cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .field { padding: 4px 0; min-height: 28px; }
    .field .label { color: #64748b; font-size: 10px; text-transform: uppercase; letter-spacing: 0.6px; font-weight: 600; }
    .field .value { color: #0f172a; font-size: 12.5px; font-weight: 500; margin-top: 2px; word-break: break-word; }
    .field .value.placeholder { color: #cbd5e1; font-weight: 400; }
    table.records, table.fees { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 11.5px; }
    table.records th, table.records td, table.fees th, table.fees td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; vertical-align: top; }
    table.records th, table.fees th { background: #fdf2f8; color: #9d174d; font-weight: 700; font-size: 10.5px; text-transform: uppercase; letter-spacing: 0.4px; }
    table.fees tr.totals td { background: #f0fdf4; font-weight: 700; color: #14532d; }
    .docs { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .doc { border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px; background: #f8fafc; min-height: 90px; display: flex; flex-direction: column; }
    .doc .doc-label { font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 0.4px; }
    .doc .doc-link  { font-size: 11px; color: #be185d; margin-top: 4px; word-break: break-all; }
    .doc .doc-link.missing { color: #cbd5e1; }
    .doc img { max-width: 100%; max-height: 70px; object-fit: contain; margin-top: 4px; }
    .signatures { display: grid; grid-template-columns: repeat(2, 1fr); gap: 32px; margin-top: 36px; }
    .signatures .sig { border-top: 1px solid #475569; padding-top: 6px; font-size: 11px; color: #475569; text-align: center; }
    .footer { margin-top: 28px; padding-top: 12px; border-top: 1px dashed #e2e8f0; font-size: 10.5px; color: #94a3b8; text-align: center; }
    .actions { max-width: 900px; margin: 0 auto 16px; display: flex; justify-content: flex-end; gap: 8px; }
    .actions button { padding: 7px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; border: 1px solid #e2e8f0; background: white; color: #475569; }
    .actions button.primary { background: #ec4899; color: white; border-color: #ec4899; }
    .actions button:hover { background: #fdf2f8; }
    .actions button.primary:hover { background: #db2777; }
    @media print {
        body { background: white; padding: 0; }
        .sheet { border: none; border-radius: 0; padding: 18px 22px; max-width: none; }
        .actions { display: none; }
    }
</style>
</head>
<body>

@php
    $val = fn ($v) => filled($v) ? e($v) : '<span class="placeholder">—</span>';
    $records = $student->academic_records ?? [];
    $totalFee  = array_sum(array_column($schedule, 'fee'));
    $totalPaid = array_sum(array_column($schedule, 'paid'));
    $totalBal  = array_sum(array_column($schedule, 'balance'));
    $docs = [
        'photo_path'                => 'Student Photo',
        'student_sign_path'         => 'Student Sign',
        'aadhar_front_path'         => 'Aadhar Front',
        'aadhar_back_path'          => 'Aadhar Back',
        'marksheet_x_path'          => 'X Marksheet',
        'marksheet_xii_path'        => 'XII Marksheet',
        'marksheet_graduation_path' => 'Graduation Marksheet',
        'abc_id_path'               => 'ABC ID',
        'deb_id_path'               => 'DEB ID',
        'other_doc_path'            => 'Other Document',
    ];
    $isImage = function (?string $url): bool {
        if (! $url) return false;
        return (bool) preg_match('/\.(png|jpe?g|gif|webp|bmp|svg)(\?|$)/i', $url);
    };
@endphp

<div class="actions">
    <button type="button" onclick="window.print()">Print</button>
    <a href="{{ route('students.form', ['student' => $student->id, 'download' => 1]) }}" style="text-decoration:none">
        <button type="button" class="primary">Download (.html)</button>
    </a>
    <button type="button" onclick="window.close()">Close</button>
</div>

<div class="sheet">
    <div class="topbar">
        <div class="photo">
            @if ($docUrls['photo_path'] ?? null)
                <img src="{{ $docUrls['photo_path'] }}" alt="Photo">
            @else
                Photo
            @endif
        </div>
        <div>
            <div class="brand">SSB Education — Admission Form</div>
            <div class="sub">University admission record · Generated for office use</div>
        </div>
        <div class="meta">
            <div><strong>Admission No:</strong> {!! $val($student->admission_no) !!}</div>
            <div><strong>Created:</strong> {{ $student->created_at?->format('d M Y') }}</div>
            <div><strong>Status:</strong> {{ $student->active ? 'Active' : 'Inactive' }}</div>
        </div>
    </div>

    <h1>{{ $student->name }}</h1>

    <h2>Course Placement</h2>
    <div class="grid">
        <div class="field"><div class="label">University / Board</div><div class="value">{!! $val($student->university?->name) !!}</div></div>
        <div class="field"><div class="label">Course</div><div class="value">{!! $val($student->course?->name) !!}</div></div>
        <div class="field"><div class="label">Mode</div><div class="value" style="text-transform:capitalize">{!! $val($student->mode) !!}</div></div>
        <div class="field"><div class="label">Type</div><div class="value" style="text-transform:capitalize">{!! $val($student->enrollment_type) !!}</div></div>
        <div class="field"><div class="label">Year</div><div class="value">{!! $val($student->course_year) !!}</div></div>
        <div class="field">
            <div class="label">Semester</div>
            <div class="value">
                @if ($student->university?->type === \App\Models\University::TYPE_BOARD)
                    <span class="placeholder">Board — year only</span>
                @else
                    {!! $val($student->semester) !!}
                @endif
            </div>
        </div>
    </div>

    <h2>Student Details</h2>
    <div class="grid">
        <div class="field"><div class="label">Student Name</div><div class="value">{!! $val($student->name) !!}</div></div>
        <div class="field"><div class="label">Father's Name</div><div class="value">{!! $val($student->father_name) !!}</div></div>
        <div class="field"><div class="label">Mother's Name</div><div class="value">{!! $val($student->mother_name) !!}</div></div>
        <div class="field"><div class="label">Gender</div><div class="value" style="text-transform:capitalize">{!! $val($student->gender) !!}</div></div>
        <div class="field"><div class="label">Date of Birth</div><div class="value">{!! $val($student->dob?->format('d M Y')) !!}</div></div>
        <div class="field"><div class="label">Category</div><div class="value" style="text-transform:uppercase">{!! $val($student->category) !!}</div></div>
        <div class="field"><div class="label">Nationality</div><div class="value">{!! $val($student->nationality) !!}</div></div>
        <div class="field"><div class="label">Religion</div><div class="value">{!! $val($student->religion) !!}</div></div>
        <div class="field"><div class="label">Aadhar Number</div><div class="value">{!! $val($student->aadhar_number) !!}</div></div>
        <div class="field"><div class="label">Mobile</div><div class="value">{!! $val($student->mobile) !!}</div></div>
        <div class="field"><div class="label">Email</div><div class="value">{!! $val($student->email) !!}</div></div>
        <div class="field"><div class="label">Added By</div><div class="value">{!! $val($student->creator?->name) !!}</div></div>
    </div>

    <h2>Address</h2>
    <div class="grid cols-2">
        <div class="field" style="grid-column: span 2"><div class="label">Address</div><div class="value" style="white-space: pre-line">{!! $val($student->address) !!}</div></div>
        <div class="field"><div class="label">City</div><div class="value">{!! $val($student->city) !!}</div></div>
        <div class="field"><div class="label">State</div><div class="value">{!! $val($student->state) !!}</div></div>
        <div class="field"><div class="label">Country</div><div class="value">{!! $val($student->country) !!}</div></div>
        <div class="field"><div class="label">Pincode</div><div class="value">{!! $val($student->pincode) !!}</div></div>
    </div>

    <h2>Academic Records</h2>
    <table class="records">
        <thead>
            <tr>
                <th style="width: 12%">Examination</th>
                <th>Board / University</th>
                <th>Subject</th>
                <th style="width: 14%">Year of Passing</th>
                <th style="width: 14%">Division / Grade</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($records as $r)
                <tr>
                    <td><strong>{{ $r['level'] ?? '' }}</strong></td>
                    <td>{!! $val($r['board']   ?? null) !!}</td>
                    <td>{!! $val($r['subject'] ?? null) !!}</td>
                    <td>{!! $val($r['year']    ?? null) !!}</td>
                    <td>{!! $val($r['grade']   ?? null) !!}</td>
                </tr>
            @empty
                @foreach (['X', 'XII', 'UG', 'PG', 'OTHER'] as $lvl)
                    <tr><td><strong>{{ $lvl }}</strong></td><td>—</td><td>—</td><td>—</td><td>—</td></tr>
                @endforeach
            @endforelse
        </tbody>
    </table>

    <h2>Fee Summary</h2>
    @if (empty($schedule))
        <p style="color:#64748b">No course linked, so no fee schedule has been generated for this student.</p>
    @else
        <table class="fees">
            <thead>
                <tr>
                    <th>Period</th>
                    <th style="width: 18%; text-align:right">Fee (₹)</th>
                    <th style="width: 18%; text-align:right">Collected (₹)</th>
                    <th style="width: 18%; text-align:right">Balance (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($schedule as $row)
                    <tr>
                        <td>{{ $row['label'] }}</td>
                        <td style="text-align:right">{{ number_format($row['fee'], 2) }}</td>
                        <td style="text-align:right">{{ number_format($row['paid'], 2) }}</td>
                        <td style="text-align:right">{{ number_format($row['balance'], 2) }}</td>
                    </tr>
                @endforeach
                <tr class="totals">
                    <td>Total</td>
                    <td style="text-align:right">{{ number_format($totalFee, 2) }}</td>
                    <td style="text-align:right">{{ number_format($totalPaid, 2) }}</td>
                    <td style="text-align:right">{{ number_format($totalBal, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <h2>Uploaded Documents</h2>
    <div class="docs">
        @foreach ($docs as $field => $label)
            @php $url = $docUrls[$field] ?? null; @endphp
            <div class="doc">
                <div class="doc-label">{{ $label }}</div>
                @if ($url && $isImage($url))
                    <img src="{{ $url }}" alt="{{ $label }}">
                    <a class="doc-link" href="{{ $url }}" target="_blank" rel="noopener">Open</a>
                @elseif ($url)
                    <a class="doc-link" href="{{ $url }}" target="_blank" rel="noopener">Open file ↗</a>
                @else
                    <div class="doc-link missing">Not uploaded</div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="signatures">
        <div class="sig">Signature of Student</div>
        <div class="sig">Signature of Authorized Officer</div>
    </div>

    <div class="footer">
        Generated {{ now()->setTimezone(config('app.timezone'))->format('d M Y · h:i A') }} ·
        SSB Education Admission Form · Student ID #{{ $student->id }}
    </div>
</div>

</body>
</html>
