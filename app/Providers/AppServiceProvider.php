<?php

namespace App\Providers;

use App\Services\ExternalServices\GoogleDriveService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\Backup\Events\BackupWasSuccessful;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(BackupWasSuccessful::class, function () {
            $files = Storage::disk('local')->files(config('backup.backup.name'));

            $sortedFiles = Arr::sort($files, function ($file) {
                return Storage::disk('local')->lastModified($file);
            });

            $latestFileRelative = end($sortedFiles);

            if ($latestFileRelative) {
                try {
                    $googleService = app(GoogleDriveService::class);
                    $file = Storage::disk('local')->path($latestFileRelative);

                    $uploadedFile = new UploadedFile($file, basename($file), 'application/zip', null, true);

                    $googleService->upload($uploadedFile, config('services.google.drive_backup_folder_id'), basename($file));
                    Log::info("Backup successfully uploaded to Google.");

                    // FIX 2: Delete the local file using the correct variable name
                    Storage::disk('local')->delete($latestFileRelative);
                    Log::info("Temporary local backup file deleted.");

                } catch (\Exception $e) {
                    Log::error("Failed during Google upload/cleanup workflow: " . $e->getMessage());
                }
            }
        });
    }
}
