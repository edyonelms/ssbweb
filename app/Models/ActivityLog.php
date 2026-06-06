<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class ActivityLog extends Model
{
    public const CATEGORY_ANNOUNCEMENT = 'announcement';
    public const CATEGORY_SUPPORT      = 'support';
    public const CATEGORY_WALLET       = 'wallet';
    public const CATEGORY_USER         = 'user';

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'summary',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an activity for the currently authenticated user. Silently
     * no-ops when the activity_logs table doesn't exist yet (before
     * migrate) so the rest of the controller flow isn't blocked.
     */
    public static function record(string $action, string $summary, $subject = null, array $meta = []): ?self
    {
        if (! auth()->check()) {
            return null;
        }

        try {
            if (! Schema::hasTable('activity_logs')) {
                return null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return static::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => is_object($subject) ? ($subject->id ?? null) : null,
            'summary'      => mb_substr($summary, 0, 500),
            'meta'         => $meta ?: null,
        ]);
    }

    public function getCategoryAttribute(): string
    {
        return explode('.', $this->action, 2)[0] ?? 'other';
    }
}
