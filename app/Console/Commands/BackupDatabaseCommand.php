<?php

namespace App\Console\Commands;

use App\Services\ExternalServices\GoogleDriveService;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;

#[Signature('app:backup-database')]
#[Description('Backup DB to Google Drive daily')]
class BackupDatabaseCommand extends Command
{
    public function __construct(private GoogleDriveService $googleDriveService)  {
        parent::__construct();
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('🔄 Starting database backup...');

            // 1. Create backup file
            $filename = 'backup_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql.gz';
            $filepath = storage_path('app/backups/' . $filename);

            if (!is_dir(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }

            // 2. Dump and compress database
            $this->dumpDatabase($filepath);
            $this->info("✓ Database backup created: {$filename}");

            // 3. Upload to Google Drive
            $fileId = $this->uploadToGoogleDrive($filepath, $filename);
            $this->info("✓ Uploaded to Google Drive (ID: {$fileId})");

            // 4. Delete local backup file
            if (file_exists($filepath)) {
                unlink($filepath);
                $this->info("✓ Local backup deleted");
            }

            // 5. Clean up old backups (older than 3 days)
            $this->deleteOldBackups();
            $this->info("✓ Old backups cleaned up");

            $this->info('✅ Backup completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }

    private function dumpDatabase(string $filepath)
    {
        // Get database credentials from config
        $host = config('database.connections.pgsql.host');
        $port = config('database.connections.pgsql.port');
        $database = config('database.connections.pgsql.database');
        $username = config('database.connections.pgsql.username');
        $password = config('database.connections.pgsql.password');

        // Create SQL backup
        $sqlFile = str_replace('.gz', '', $filepath);

        $command = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -F c -b %s > %s 2>&1',
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($sqlFile),
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Database dump failed' . (implode("\n", $output) ?: 'Unknown error'));
        }

        // Compress with gzip
        exec('gzip -f ' . escapeshellarg($sqlFile), $output, $returnCode);

        if (!file_exists($filepath)) {
            throw new \Exception('Backup compression failed' . (implode("\n", $output) ?: 'Unknown error'));
        }
    }

    private function uploadToGoogleDrive(string $localPath, string $filename)
    {
        // Mime type for .gz files
        // Mark as test mode so Laravel bypasses HTTP upload validation
        $uploadedFile = new UploadedFile($localPath, $filename, 'application/x-gzip', null, true);
        return $this->googleDriveService->upload($uploadedFile, config('services.google.drive_backup_folder_id'), $filename);
    }

    private function deleteOldBackups()
    {
        try {
            // Calculate cutoff date (3 days ago)
            $retentionDays = config('backup.retention_days', 3);
            $cutoffDate = Carbon::now()->subDays($retentionDays);


            $files = $this->googleDriveService->getFiles('',config('services.google.drive_backup_folder_id'), 'id');

            // Delete old backups
            foreach ($files as $file) {
                $createdTime = Carbon::parse($file->createdTime);

                if ($createdTime->lessThan($cutoffDate)) {
                    $this->googleDriveService->delete($file->id);
                    $this->info("🗑️ Deleted old backup: " . $file->name);
                }
            }

        } catch (\Exception $e) {
            $this->warn("Could not clean old backups: " . $e->getMessage());
        }
    }
}
