<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SupportReply extends Model
{
    protected $fillable = [
        'support_query_id',
        'user_id',
        'message',
        'file_path',
        'file_original_name',
    ];

    protected $appends = ['file_url'];

    public function supportQuery(): BelongsTo
    {
        return $this->belongsTo(SupportQuery::class, 'support_query_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->url($this->file_path);
        }
        return null;
    }
}
