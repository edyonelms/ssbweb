<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    public const MODES = ['cash', 'upi', 'cheque', 'online', 'neft'];

    protected $fillable = [
        'user_id',
        'amount',
        'mode',
        'note',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function balanceFor(int $userId): float
    {
        return (float) static::where('user_id', $userId)->sum('amount');
    }
}
