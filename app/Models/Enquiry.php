<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_APPROVED  = 'approved';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONTACTED,
        self::STATUS_APPROVED,
    ];

    public const SOURCE_WEB   = 'web';
    public const SOURCE_ADMIN = 'admin';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'status',
        'source',
        'admin_notes',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];
}
