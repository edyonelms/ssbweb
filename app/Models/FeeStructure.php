<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model
{
    protected $fillable = [
        'university_id',
        'course_id',
        'fee_per_sem',
    ];

    protected $casts = [
        'fee_per_sem' => 'decimal:2',
    ];

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function totalFee(): float
    {
        $semesters = $this->course?->semesterCount() ?? 0;
        return (float) $this->fee_per_sem * $semesters;
    }
}
