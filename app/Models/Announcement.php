<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Announcement extends Model
{
    public const AUDIENCE_ALL = 'all';
    public const AUDIENCE_SELECTED = 'selected';

    protected $fillable = [
        'heading',
        'description',
        'file_path',
        'file_original_name',
        'audience',
        'created_by',
    ];

    protected $appends = ['file_url'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('read_at');
    }

    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            return Storage::disk('public')->url($this->file_path);
        }
        return null;
    }

    public function isForAll(): bool
    {
        return $this->audience === self::AUDIENCE_ALL;
    }
}
