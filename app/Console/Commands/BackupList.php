<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * List objects on the backup disk so the operator can quickly find the
 * archive they want to restore from.
 *
 *   php artisan backup:list                # default: most recent DB dumps
 *   php artisan backup:list db             # all DB dumps, newest first
 *   php artisan backup:list files          # all mirrored files
 *   php artisan backup:list --recursive    # recurse into sub-folders
 *   php artisan backup:list db --limit=50  # show 50 entries
 */
class BackupList extends Command
{
    protected $signature = 'backup:list
                            {prefix=db : Path prefix to list (db, files, healthcheck, …)}
                            {--recursive : Include nested folders}
                            {--limit=20 : Cap the table at this many rows (0 = unlimited)}';

    protected $description = 'List backup objects on the S3 backup disk.';

    public function handle(): int
    {
        if (empty(config('filesystems.disks.backup.bucket'))) {
            $this->error('AWS_BACKUP_BUCKET is not configured.');
            return self::FAILURE;
        }

        $disk   = Storage::disk('backup');
        $prefix = trim($this->argument('prefix'), '/');
        $limit  = (int) $this->option('limit');

        try {
            $files = $this->option('recursive')
                ? $disk->allFiles($prefix)
                : $disk->files($prefix);
        } catch (\Throwable $e) {
            $this->error('Could not list backup disk: '.$e->getMessage());
            return self::FAILURE;
        }

        if (empty($files)) {
            $this->info("No backups found under '{$prefix}/'.");
            return self::SUCCESS;
        }

        // Sort newest-first so the most useful entries are at the top.
        usort($files, function ($a, $b) use ($disk) {
            try {
                return $disk->lastModified($b) <=> $disk->lastModified($a);
            } catch (\Throwable $e) {
                return strcmp($b, $a);
            }
        });

        if ($limit > 0 && count($files) > $limit) {
            $truncated = count($files) - $limit;
            $files = array_slice($files, 0, $limit);
        }

        $rows = [];
        foreach ($files as $path) {
            try {
                $size = $disk->size($path);
                $when = $disk->lastModified($path);
            } catch (\Throwable $e) {
                $size = 0;
                $when = 0;
            }
            $rows[] = [
                $path,
                $this->formatBytes($size),
                $when ? date('Y-m-d H:i:s', $when) : '—',
            ];
        }

        $this->table(['Path', 'Size', 'Last Modified'], $rows);

        $total = count($files);
        if (isset($truncated)) {
            $this->comment("Showing top {$total} (truncated — {$truncated} more — pass --limit=0 to see all).");
        } else {
            $this->comment("Total: {$total} object(s).");
        }
        return self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $exp = (int) floor(log($bytes, 1024));
        $exp = min($exp, count($units) - 1);
        return number_format($bytes / pow(1024, $exp), 2).' '.$units[$exp];
    }
}
