<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    /**
     * Reverse link to the funds request that produced this transaction,
     * if any. Lets the All Transactions listing surface "Reason: <topic>"
     * for credits that came from an approved Ask Payment request.
     */
    public function paymentRequest(): HasOne
    {
        return $this->hasOne(PaymentRequest::class, 'wallet_transaction_id');
    }

    /**
     * Human-readable reason for the transaction — the linked request's
     * topic when present, otherwise the manual note. Empty string when
     * neither is set.
     */
    public function getReasonAttribute(): string
    {
        $topic = $this->paymentRequest?->topic;
        return $topic ?: ($this->note ?: '');
    }

    public static function balanceFor(int $userId): float
    {
        return (float) static::where('user_id', $userId)->sum('amount');
    }
}
