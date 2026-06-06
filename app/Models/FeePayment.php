<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    public const MODES = ['cash', 'upi', 'cheque', 'online', 'neft'];

    protected $fillable = [
        'student_id',
        'semester',
        'amount',
        'mode',
        'collected_by_name',
        'remark',
        'recorded_by',
        'batch_id',
        'paid_at',
    ];

    protected $casts = [
        'amount'   => 'decimal:2',
        'semester' => 'integer',
        'paid_at'  => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getSemesterLabelAttribute(): string
    {
        return $this->semester === 0 ? 'Registration' : 'Semester '.$this->semester;
    }
}
