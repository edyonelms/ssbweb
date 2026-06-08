<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Student extends Model
{
    public const CATEGORIES = ['general', 'obc', 'sc', 'st', 'minor', 'nri', 'other'];

    /** Mirror of the document fields the admission form collects. */
    public const DOCUMENT_FIELDS = [
        'photo_path',
        'aadhar_front_path',
        'aadhar_back_path',
        'marksheet_x_path',
        'marksheet_xii_path',
        'marksheet_graduation_path',
        'student_sign_path',
        'abc_id_path',
        'deb_id_path',
        'other_doc_path',
    ];

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'admission_no',
        'class_name',
        'university_id',
        'course_id',
        'mode',
        'enrollment_type',
        'course_year',
        'semester',
        'father_name',
        'mother_name',
        'dob',
        'category',
        'nationality',
        'religion',
        'aadhar_number',
        'gender',
        'parent_name',
        'address',
        'country',
        'state',
        'city',
        'pincode',
        'photo_path',
        'aadhar_front_path',
        'aadhar_back_path',
        'marksheet_x_path',
        'marksheet_xii_path',
        'marksheet_graduation_path',
        'student_sign_path',
        'abc_id_path',
        'deb_id_path',
        'other_doc_path',
        'academic_records',
        'active',
        'created_by',
    ];

    protected $casts = [
        'active'           => 'boolean',
        'dob'              => 'date',
        'academic_records' => 'array',
        'course_year'      => 'integer',
        'semester'         => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function feePayments(): HasMany
    {
        return $this->hasMany(FeePayment::class)->orderBy('semester')->orderByDesc('paid_at');
    }

    /**
     * Build a URL for a stored document path; null when the file is
     * missing so callers can render a graceful placeholder.
     */
    public function documentUrl(string $field): ?string
    {
        $path = $this->{$field} ?? null;
        if (! $path) {
            return null;
        }
        try {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->url($path);
            }
        } catch (\Throwable $e) {
            // Disk misconfigured — fall through and return null.
        }
        return null;
    }

    /**
     * Returns a per-semester fee breakdown:
     *   [
     *     ['semester' => 0, 'label' => 'Registration', 'fee' => 5000.00, 'paid' => 5000, 'balance' => 0],
     *     ['semester' => 1, 'label' => 'Semester 1',   'fee' => 10000.00, 'paid' => 7000, 'balance' => 3000],
     *     ...
     *   ]
     *
     * Returns an empty array when the student isn't linked to a course.
     */
    public function feeSchedule(): array
    {
        $course = $this->course;
        if (! $course) {
            return [];
        }

        $rows = [];
        $payments = $this->feePayments()->get()->groupBy('semester');

        $regFee = (float) $course->registration_fee;
        if ($regFee > 0) {
            $paid = (float) ($payments[0] ?? collect())->sum('amount');
            $rows[] = [
                'semester' => 0,
                'label'    => 'Registration',
                'fee'      => $regFee,
                'paid'     => $paid,
                'balance'  => max(0, $regFee - $paid),
            ];
        }

        // Boards charge annually, universities per semester. The semester
        // column on fee_payments doubles as the period index (1 = first
        // year for boards / first semester for universities) so existing
        // payments line up with the new schedule layout.
        $periodFee   = (float) $course->fee_per_sem;
        $periodCount = $course->feePeriodCount();
        $isBoard     = $course->isBoard();
        $unitLabel   = $isBoard ? 'Year' : 'Semester';
        for ($i = 1; $i <= $periodCount; $i++) {
            $paid = (float) ($payments[$i] ?? collect())->sum('amount');
            $rows[] = [
                'semester' => $i,
                'label'    => $unitLabel.' '.$i,
                'fee'      => $periodFee,
                'paid'     => $paid,
                'balance'  => max(0, $periodFee - $paid),
            ];
        }

        return $rows;
    }
}
