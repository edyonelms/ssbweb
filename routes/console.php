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
