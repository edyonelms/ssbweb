<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Nightly disaster-recovery backup.
 *
 *   php artisan backup:run          # DB dump + file mirror
 *   php artisan backup:run --db     # DB dump only
 *   php artisan backup:run --files  # file mirror only
 *   php artisan backup:run --keep-local  # keep the temp dump on disk after upload
 *
 * Writes everything to the "backup" filesystem disk (configured in
 * config/filesystems.php), which should be a dedicated S3 bucket with
 * Versioning + Default Encryption + Block Public Access turned on.
 *
 * The layout on the backup disk looks like:
 *   db/db_YYYY-MM-DD_HHMMSS.sql.gz       — one gzipped dump per night
 *   files/<original-path>                — every uploaded asset, mirrored
 *   files/_manifest_YYYY-MM-DD_HHMMSS.txt — what the last sync touched
 *
 * The DB dump is a brand-new object every run (versioning isn't needed
 * because of the timestamp). The file mirror is *incremental* — files
 * already present with the same byte length are skipped. Combined with
 * bucket versioning, a deleted or replaced file is still recoverable
 * via the previous version.
 */
class BackupRun extends Command
{
    protected $signature = 'backup:run
                            {--db        : Only back up the database, skip files}
                            {--files     : Only back up the files, skip the database}
                            {--keep-local: Keep the temp DB dump on the local disk after upload}';

    protected $description = 'Snapshot the database and uploaded files to the AWS S3 backup bucket.';

    public function handle(): int
    {
        $start     = now();
        $timestamp = $start->format('Y-m-d_His');

        $this->info('Backup run started at '.$start->toDateTimeString().' ('.config('app.timezone').')');

        if (! $this->ensureBackupDiskUsable()) {
            return self::FAILURE;
        }

        $doDb    = $this->option('db')    || ! $this->option('files');
        $doFiles = $this->option('files') || ! $this->option('db');

        $okDb    = true;
        $okFiles = true;

        if ($doDb) {
            $okDb = $this->backupDatabase($timestamp);
        }
        if ($doFiles) {
            $okFiles = $this->backupFiles($timestamp);
        }

        $seconds = (int) abs(now()->diffInSeconds($start));
        $this->newLine();
        $this->info("Backup run finished in {$seconds}s — db: "
            .($okDb ? 'ok' : 'FAILED').' / files: '.($okFiles ? 'ok' : 'FAILED'));

        return ($okDb && $okFiles) ? self::SUCCESS : self::FAILURE;
    }

    // ────────────────────────────────────────────────────────────────────
    //  Pre-flight
    // ────────────────────────────────────────────────────────────────────

    private function ensureBackupDiskUsable(): bool
    {
        $bucket = config('filesystems.disks.backup.bucket');
        if (empty($bucket)) {
            $this->error('AWS_BACKUP_BUCKET is not configured. Set it in .env and retry.');
            return false;
        }

        try {
            // Touch a heartbeat file — proves the credentials work and we
            // can write to the bucket before we spend time on a DB dump.
            Storage::disk('backup')->put('healthcheck/last-backup-run.txt',
                'last backup attempt: '.now()->toIso8601String().PHP_EOL);
            $this->line("Backup disk reachable — bucket: {$bucket}");
            return true;
        } catch (\Throwable $e) {
            $this->error('Backup disk not reachable: '.$e->getMessage());
            Log::error('backup:run pre-flight failed', ['err' => $e->getMessage()]);
            return false;
        }
    }

    // ────────────────────────────────────────────────────────────────────
    //  Database
    // ────────────────────────────────────────────────────────────────────

    private function backupDatabase(string $timestamp): bool
    {
        $connection = config('database.default');
        $config     = config("database.connections.{$connection}");
        $driver     = $config['driver'] ?? null;

        $this->newLine();
        $this->line("→ DB backup ({$driver} connection: {$connection})");

        $tmpDir = storage_path('app/backup-tmp');
        if (! is_dir($tmpDir)) {
            @mkdir($tmpDir, 0755, true);
        }
        $tmpPath = $tmpDir.DIRECTORY_SEPARATOR."db_{$timestamp}.sql.gz";
        $remote  = "db/db_{$timestamp}.sql.gz";

        try {
            match ($driver) {
                'mysql', 'mariadb' => $this->dumpMysql($config, $tmpPath),
                'pgsql'            => $this->dumpPostgres($config, $tmpPath),
                'sqlite'           => $this->dumpSqlite($config, $tmpPath),
                default            => throw new \RuntimeException("Unsupported DB driver for backups: {$driver}"),
            };

            $size = (int) @filesize($tmpPath);
            $this->line('  dump created: '.$this->formatBytes($size));

            $stream = fopen($tmpPath, 'rb');
            Storage::disk('backup')->writeStream($remote, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            $this->info('  uploaded → '.$remote);

            if (! $this->option('keep-local')) {
                @unlink($tmpPath);
            }
            return true;
        } catch (\Throwable $e) {
            $this->error('  DB backup failed: '.$e->getMessage());
            Log::error('backup:run DB failure', ['err' => $e->getMessage()]);
            // Don't leave a half-written dump behind — it could be mistaken
            // for a valid backup later.
            if (file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
            return false;
        }
    }

    private function dumpMysql(array $cfg, string $outputPath): void
    {
        // We pipe `mysqldump | gzip` through a single shell so the SQL
        // is streamed straight into compression — never materializing
        // the uncompressed dump on disk. --single-transaction gives a
        // consistent snapshot of InnoDB tables without locking writers.
        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s '
            .'--single-transaction --quick --lock-tables=false '
            .'--default-character-set=utf8mb4 --routines --triggers --no-tablespaces %s '
            .'| gzip > %s',
            escapeshellarg($cfg['host']     ?? '127.0.0.1'),
            escapeshellarg((string) ($cfg['port']     ?? '3306')),
            escapeshellarg($cfg['username'] ?? ''),
            escapeshellarg((string) ($cfg['password'] ?? '')),
            escapeshellarg($cfg['database'] ?? ''),
            escapeshellarg($outputPath)
        );

        $process = Process::fromShellCommandline($cmd);
        $process->setTimeout(3600); // up to an hour for very large DBs
        $process->mustRun();
    }

    private function dumpPostgres(array $cfg, string $outputPath): void
    {
        // Pass the password via PGPASSWORD env var so it doesn't show
        // up in `ps`.
        $cmd = sprintf(
            'pg_dump -h %s -p %s -U %s -d %s --no-owner --no-acl | gzip > %s',
            escapeshellarg($cfg['host']     ?? '127.0.0.1'),
            escapeshellarg((string) ($cfg['port']     ?? '5432')),
            escapeshellarg($cfg['username'] ?? ''),
            escapeshellarg($cfg['database'] ?? ''),
            escapeshellarg($outputPath)
        );

        $process = Process::fromShellCommandline($cmd, null, ['PGPASSWORD' => (string) ($cfg['password'] ?? '')]);
        $process->setTimeout(3600);
        $process->mustRun();
    }

    private function dumpSqlite(array $cfg, string $outputPath): void
    {
        $dbPath = $cfg['database'] ?? '';
        if (! file_exists($dbPath)) {
            throw new \RuntimeException("SQLite database file not found at {$dbPath}");
        }

        // Gzip the file inline — no shell needed, works on every host.
        $in  = fopen($dbPath, 'rb');
        $out = gzopen($outputPath, 'wb9');
        if (! $in || ! $out) {
            throw new \RuntimeException('Could not open SQLite source / gzip output.');
        }
        while (! feof($in)) {
            gzwrite($out, fread($in, 8192));
        }
        fclose($in);
        gzclose($out);
    }

    // ────────────────────────────────────────────────────────────────────
    //  Files (uploads mirror)
    // ────────────────────────────────────────────────────────────────────

    private function backupFiles(string $timestamp): bool
    {
        $this->newLine();
        $this->line('→ File mirror (public disk → backup disk)');

        try {
            $source = Storage::disk('public');
            $backup = Storage::disk('backup');

            $files = $source->allFiles();
            $total = count($files);
            $this->line("  source files: {$total}");

            $uploaded = 0;
            $skipped  = 0;
            $failed   = 0;

            $bar = $this->output->createProgressBar($total);
            $bar->start();

            foreach ($files as $path) {
                $remote = 'files/'.$path;

                // Incremental: skip files already mirrored at the same
                // byte length. Hash-based comparison would be safer but
                // wildly more expensive on large uploads.
                try {
                    $srcSize = $source->size($path);
                    if ($backup->exists($remote) && $backup->size($remote) === $srcSize) {
                        $skipped++;
                        $bar->advance();
                        continue;
                    }
                } catch (\Throwable $e) {
                    // size() / exists() failed — fall through and try to upload.
                }

                try {
                    $stream = $source->readStream($path);
                    if (! is_resource($stream)) {
                        $failed++;
                        $bar->advance();
                        continue;
                    }
                    $backup->writeStream($remote, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                    $uploaded++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::warning('backup:run failed to mirror '.$path.': '.$e->getMessage());
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            // Manifest so a future restore knows what the snapshot looked
            // like — handy when paired with the matching DB dump.
            $manifest = sprintf(
                "Files mirror taken at %s\nSource files: %d\nUploaded: %d\nSkipped (unchanged): %d\nFailed: %d\n",
                $timestamp, $total, $uploaded, $skipped, $failed
            );
            $backup->put("files/_manifest_{$timestamp}.txt", $manifest);

            $this->info("  uploaded: {$uploaded}, unchanged: {$skipped}, failed: {$failed}");

            return $failed === 0;
        } catch (\Throwable $e) {
            $this->error('  Files mirror failed: '.$e->getMessage());
            Log::error('backup:run files failure', ['err' => $e->getMessage()]);
            return false;
        }
    }

    // ────────────────────────────────────────────────────────────────────
    //  Helpers
    // ────────────────────────────────────────────────────────────────────

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $exp = (int) floor(log($bytes, 1024));
        $exp = min($exp, count($units) - 1);
        return number_format($bytes / pow(1024, $exp), 2).' '.$units[$exp];
    }
}
