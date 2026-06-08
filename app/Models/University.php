<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class University extends Model
{
    public const TYPE_UNIVERSITY = 'university';
    public const TYPE_BOARD = 'board';

    protected $fillable = [
        'name',
        'image_path',
        'naac_image_path',
        'address',
        'type',
        'website',
        'registration_fee',
    ];

    protected $casts = [
        'registration_fee' => 'decimal:2',
    ];

    protected $appends = ['image_url', 'naac_image_url'];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return Storage::disk('public')->url($this->image_path);
        }
        return null;
    }

    public function getNaacImageUrlAttribute(): ?string
    {
        if ($this->naac_image_path && Storage::disk('public')->exists($this->naac_image_path)) {
            return Storage::disk('public')->url($this->naac_image_path);
        }
        return null;
    }
}
