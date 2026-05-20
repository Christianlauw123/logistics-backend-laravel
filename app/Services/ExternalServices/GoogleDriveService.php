<?php

namespace App\Services\ExternalServices;

use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleDriveService
{
    private GoogleDrive $drive;
    private string $rootFolderId;

    public function __construct()
    {
        $client = new GoogleClient();

        // Auth via service account JSON from env — no file on disk needed
        $credentials = json_decode(env('GOOGLE_SERVICE_ACCOUNT_JSON'), true);
        $client->setAuthConfig($credentials);
        $client->addScope(GoogleDrive::DRIVE);

        $this->drive        = new GoogleDrive($client);
        $this->rootFolderId = env('GOOGLE_DRIVE_FOLDER_ID');
    }

    /**
     * Upload a file to Drive.
     * Returns drive_file_id, file_url, original_filename, mime_type.
     */
    public function upload(UploadedFile $file, string $filename = '', string $subFolder = ''): array
    {
        $folderId = $subFolder ? $this->ensureFolder($subFolder, $this->rootFolderId) : $this->rootFolderId;

        $filename = $filename . '.' . $file->getClientOriginalExtension();

        // File metadata
        $metadata = new DriveFile();
        $metadata->name = $filename;
        $metadata->parents = [$folderId];

        // Upload — multipart sends metadata + file content in one request
        $uploaded = $this->drive->files->create(
            $metadata,
            [
                'data'       => file_get_contents($file->getRealPath()),
                'mimeType'   => $file->getMimeType(),
                'uploadType' => 'multipart',
                'fields'     => 'id, name, mimeType, webViewLink',
            ]
        );

        $fileId = $uploaded->getId();

        // Make file viewable by anyone with the link
        // $this->setPublicReadable($fileId);

        return [
            'drive_file_id'     => $fileId,
            'file_url'          => "https://drive.google.com/file/d/{$fileId}/view",
            'original_filename' => $file->getClientOriginalName(),
            'mime_type'         => $file->getMimeType(),
        ];
    }

    /**
     * Delete a file from Drive by its file ID.
     */
    public function delete(string $fileId): bool
    {
        try {
            $this->drive->files->delete($fileId);
            return true;
        } catch (Throwable $e) {
            Log::warning("GoogleDrive delete failed: {$e->getMessage()}", ['file_id' => $fileId]);
            return false;
        }
    }

    /**
     * Find or create a subfolder inside a parent folder.
     * Returns the folder ID.
     * Used to organise: transactions/42/filename.pdf
     */
    private function ensureFolder(string $folderName, string $parentId): string
    {
        // Search for existing folder first — avoid duplicates
        $results = $this->drive->files->listFiles([
            'q'      => "name='{$folderName}'"
                      . " and '{$parentId}' in parents"
                      . " and mimeType='application/vnd.google-apps.folder'"
                      . " and trashed=false",
            'fields' => 'files(id)',
        ]);
        dd($results);


        $files = $results->getFiles();

        if (! empty($files)) {
            return $files[0]->getId();
        }

        $metadata = new DriveFile();
        $metadata->name = $folderName;
        $metadata->mimeType = 'application/vnd.google-apps.folder';
        $metadata->parents = [$parentId];

        // Create it
        $folder = $this->drive->files->create($metadata, ['fields' => 'id']);

        return $folder->getId();
    }

    /**
     * Set file permission to public readable (anyone with link).
     */
    // private function setPublicReadable(string $fileId): void
    // {
    //     $this->drive->permissions->create(
    //         $fileId,
    //         new Permission([
    //             'type' => 'anyone',
    //             'role' => 'reader',
    //         ])
    //     );
    // }
}
