<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOldAnnouncements extends Command
{
    protected $signature = 'announcements:cleanup {--days=60 : Maximum age in days}';

    protected $description = 'Delete announcements older than the configured age (default 60 days).';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $stale = Announcement::where('created_at', '<', $cutoff)->get();
        $count = 0;

        foreach ($stale as $a) {
            if ($a->file_path && Storage::disk('public')->exists($a->file_path)) {
                Storage::disk('public')->delete($a->file_path);
            }
            $a->recipients()->detach();
            $a->delete();
            $count++;
        }

        $this->info("Deleted {$count} announcement(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
