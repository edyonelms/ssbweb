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
        'duration_years',
        'lateral_entry',
        'subjects',
    ];

    protected $casts = [
        'duration_years' => 'decimal:1',
        'lateral_entry'  => 'boolean',
    ];

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
}
