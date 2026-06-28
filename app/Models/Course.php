<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Course extends Model
{
    protected $fillable = [
        'university_id',
        'name',
        'mode',
        'enrollment_type',
        'duration_years',
        'registration_fee',
        'fee_per_sem',
        'current_semester',
        'lateral_entry',
        'subjects',
    ];

    protected $casts = [
        'duration_years'   => 'decimal:1',
        'registration_fee' => 'decimal:2',
        'fee_per_sem'      => 'decimal:2',
        'current_semester' => 'integer',
        'lateral_entry'    => 'boolean',
    ];

    /**
     * Convenience for the Upgrade Semester tab — boards track current
     * "Year N", universities track "Semester N". Same column, different
     * vocabulary.
     */
    public function currentPeriodLabel(): string
    {
        $unit = $this->isBoard() ? 'Year' : 'Semester';
        return $unit.' '.max(1, (int) ($this->current_semester ?? 1));
    }

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function feeStructure(): HasOne
    {
        return $this->hasOne(FeeStructure::class);
    }

    public function semesterCount(): int
    {
        // Each year = 2 semesters; round up for half-year courses.
        return (int) ceil(((float) $this->duration_years) * 2);
    }

    public function isBoard(): bool
    {
        return $this->university?->type === University::TYPE_BOARD;
    }

    // Boards charge per year; universities charge per semester. The
    // course's fee_per_sem column doubles as the annual fee for boards.
    public function feePeriodCount(): int
    {
        return $this->isBoard()
            ? (int) ceil((float) $this->duration_years)
            : $this->semesterCount();
    }

    public function feePeriodLabel(): string
    {
        return $this->isBoard() ? 'Annual' : 'Semester';
    }

    public function totalFee(): float
    {
        return (float) $this->fee_per_sem * $this->feePeriodCount() + (float) $this->registration_fee;
    }
}
