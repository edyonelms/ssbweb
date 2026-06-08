<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Pull a specific backup object off S3 to local disk so the operator
 * can decompress / restore it.
 *
 *   php artisan backup:download db/db_2026-06-09_022100.sql.gz
 *   php artisan backup:download db/db_2026-06-09_022100.sql.gz --out=/tmp/x.sql.gz
 *
 * After download:
 *   gunzip < storage/app/backup-restored/db_*.sql.gz | mysql -u user -p database
 */
class BackupDownload extends Command
{
    protected $signature = 'backup:download
                            {path : Path on the backup disk to download (e.g. db/db_2026-06-09_022100.sql.gz)}
                            {--out= : Local output path (defaults to storage/app/backup-restored/<basename>)}';

    protected $description = 'Download one backup object from the S3 backup disk for restore.';

    public function handle(): int
    {
        if (empty(config('filesystems.disks.backup.bucket'))) {
            $this->error('AWS_BACKUP_BUCKET is not configured.');
            return self::FAILURE;
        }

        $path = $this->argument('path');
        $disk = Storage::disk('backup');

        try {
            if (! $disk->exists($path)) {
                $this->error("Not found on backup disk: {$path}");
                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error('Could not stat object: '.$e->getMessage());
            return self::FAILURE;
        }

        $out = $this->option('out') ?: storage_path('app/backup-restored/'.basename($path));
        $outDir = dirname($out);
        if (! is_dir($outDir)) {
            @mkdir($outDir, 0755, true);
        }

        try {
            $in = $disk->readStream($path);
            if (! is_resource($in)) {
                $this->error('Could not open remote stream.');
                return self::FAILURE;
            }
            $fp = fopen($out, 'wb');
            if (! $fp) {
                $this->error("Could not open local output path: {$out}");
                if (is_resource($in)) fclose($in);
                return self::FAILURE;
            }
            $bytes = 0;
            while (! feof($in)) {
                $chunk = fread($in, 8192);
                if ($chunk === false) break;
                fwrite($fp, $chunk);
                $bytes += strlen($chunk);
            }
            fclose($fp);
            if (is_resource($in)) fclose($in);
        } catch (\Throwable $e) {
            $this->error('Download failed: '.$e->getMessage());
            return self::FAILURE;
        }

        $this->info("Downloaded {$path} → {$out} ({$this->formatBytes($bytes)})");
        $this->newLine();
        $this->comment('Next steps:');
        if (str_ends_with($path, '.sql.gz')) {
            $this->line('  • Inspect:   gunzip -c '.escapeshellarg($out).' | head -40');
            $this->line('  • Restore MySQL:  gunzip < '.escapeshellarg($out).' | mysql -u <user> -p <database>');
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
