<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PaymentRequest extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    protected $fillable = [
        'user_id',
        'amount',
        'approved_amount',
        'topic',
        'screenshot_path',
        'status',
        'decided_by',
        'decided_at',
        'wallet_transaction_id',
        'admin_note',
    ];

    protected $casts = [
        'amount'          => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'decided_at'      => 'datetime',
    ];

    protected $appends = ['screenshot_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class);
    }

    public function getScreenshotUrlAttribute(): ?string
    {
        if ($this->screenshot_path && Storage::disk('public')->exists($this->screenshot_path)) {
            return Storage::disk('public')->url($this->screenshot_path);
        }
        return null;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
