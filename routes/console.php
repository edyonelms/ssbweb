<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reap announcements older than 60 days every night so the listing
// stays bounded without admin intervention.
Schedule::command('announcements:cleanup')->dailyAt('02:00');

// Nightly disaster-recovery backup — gzipped DB dump + incremental
// mirror of every uploaded file to the dedicated S3 backup bucket.
// withoutOverlapping() keeps the next tick from piling up on top of
// a still-running sync; runInBackground() returns control to the
// scheduler immediately. Set the server-side cron up once and forget:
//
//     * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('backup:run')
    ->dailyAt('02:30')
    ->withoutOverlapping(60)
    ->runInBackground()
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Scheduled backup:run failed — check storage logs.');
    });
