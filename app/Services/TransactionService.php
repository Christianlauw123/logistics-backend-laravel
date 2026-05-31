<?php

namespace App\Services;

use App\Jobs\ExportTransactionsJob;
use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\Repositories\TripPriceRepository;
use App\Services\ExternalServices\GoogleDriveService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TransactionService
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly TripPriceRepository $tripPriceRepository,
        private readonly GoogleDriveService $googleDriveService,
    ) {}

    public function list(array $filters, array $sort, int $perPage): LengthAwarePaginator
    {
        return $this->transactionRepository->paginate($filters, $sort, $perPage);
    }

    public function findOrFail(string $id): Transaction
    {
        return $this->transactionRepository->findByIdOrFail($id);
    }

    public function create(array $data, string $userId): Transaction
    {
        // Check TripPrice if exists
        $filters = [
            'customer_id' => $data['customer_id'],
            'origin_sub_district_id' => $data['origin_sub_district_id'],
            'dest_sub_district_id' => $data['dest_sub_district_id']
        ];
        $tripPriceCheck = $this->tripPriceRepository->paginate($filters);

        if (count($tripPriceCheck->items()) <= 0) {
            throw ValidationException::withMessages([
                'trip_price' => 'Base price for this customer and route not exists.',
            ]);
        }

        $transactionData = collect($data)
            ->merge(['user_id' => $userId, 'trip_price_id' => $tripPriceCheck->items()[0]->id])
            ->toArray();

        $transaction = $this->transactionRepository->create($transactionData);

        // Create Drive Folder
        $subFolder = 'PENDING_DO_NUMBER';
        if($transaction->do_number !== null)
            $subFolder = $transaction->do_number;
        $subFolder = 'transactions;'.$transaction->do_date.';'.$subFolder.';'.$transaction->customer_name.';'.$transaction->id;

        $this->transactionRepository->prePopulateTransaction($transaction->id);
        $this->transactionRepository->prePopulateCreateTransaction($transaction->id);

        // Set file_provider & file_folder_id to db
        // Create Folder Parent
        $transactionFolder = $this->googleDriveService->createFolder($subFolder);
        $transactionDetailFolder = $this->googleDriveService->createFolder('transaction_details', $transactionFolder);
        $this->transactionRepository->setGoogleDriveFolder($transaction->id, $transactionFolder, $transactionDetailFolder);

        return $transaction;
    }

    public function update(string $id, array $data): Transaction
    {
        // Check TripPrice if exists
        $filters = [
            'customer_id' => $data['customer_id'],
            'origin_sub_district_id' => $data['origin_sub_district_id'],
            'dest_sub_district_id' => $data['dest_sub_district_id']
        ];
        $tripPriceCheck = $this->tripPriceRepository->paginate($filters);

        if (count($tripPriceCheck->items()) <= 0) {
            throw ValidationException::withMessages([
                'trip_price' => 'Base price for this customer and route not exists.',
            ]);
        }

        $transaction = $this->transactionRepository->findByIdOrFail($id);

        // Business rule: only SUBMITTED transactions can be edited
        if ($transaction->status !== 'SUBMITTED') {
            throw ValidationException::withMessages([
                'status' => 'Only SUBMITTED transactions can be edited.',
            ]);
        }

        $this->transactionRepository->update($transaction, $data);
        $this->transactionRepository->prePopulateTransaction($transaction->id);
        $transaction->refresh();

        // Update Drive Folder Name
        $subFolder = 'PENDING_DO_NUMBER';
        if($transaction->do_number !== null)
            $subFolder = $transaction->do_number;
        $subFolder = 'transactions;'.$transaction->do_date.';'.$subFolder.';'.$transaction->customer_name.';'.$transaction->id;
        $this->googleDriveService->renameFolder($transaction->file_folder_id, $subFolder);

        return $transaction;
    }

    public function changeStatus(string $id, string $status): Transaction
    {
        $transaction = $this->transactionRepository->findByIdOrFail($id);

        // Business rule: status must follow order
        $allowedTransitions = [
            'SUBMITTED' => ['APPROVED', 'DONE', 'CANCELLED', 'REJECTED'],
            'APPROVED'  => ['DONE', 'CANCELLED', 'REJECTED'],
            'DONE'      => [],
            'CANCELLED' => [],
            'REJECTED'  => [],
        ];

        $current = $transaction->status;

        if (! in_array($status, $allowedTransitions[$current], true)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition from {$current} to {$status}.",
            ]);
        }

        return $this->transactionRepository->updateStatus($transaction, $status);
    }

    public function delete(string $id): void
    {
        $transaction = $this->transactionRepository->findByIdOrFail($id);

        // Business rule: only SUBMITTED can be deleted
        if($transaction->file_folder_id)
            $this->googleDriveService->delete($transaction->file_folder_id);

        $this->transactionRepository->delete($transaction);
    }

    // Export Services
    public function export(array $filters, array $sort): JsonResponse
    {
        // Create unique job ID
        $jobId = uniqid('export_transactions', true);
        $filePath = "exports/transactions/{$jobId}.xlsx";

        // Dispatch the export job to the queue
        ExportTransactionsJob::dispatch($filters, $sort, $filePath, $jobId)->onQueue('exports-transactions'); // Use a dedicated queue

        return response()->json([
            'success' => true,
            'job_id' => $jobId,
            'message' => 'Export job queued. Check status with this job ID.',
        ], 202); // 202 Accepted
    }

    /**
     * Check export status
     */
    public function checkStatus(string $jobId): JsonResponse
    {
        $filePath = "exports/transactions/{$jobId}.xlsx";
        $statusPath = "exports/transactions/{$jobId}.status";

        // Check if export is complete
        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($statusPath);
            return response()->json([
                'status' => 'completed',
                'job_id' => $jobId,
            ]);
        }

        // Check if there's an error status file
        if (Storage::disk('local')->exists($statusPath)) {
            $status = json_decode(Storage::disk('local')->get($statusPath), true);
            return response()->json($status);
        }

        // Still processing
        return response()->json([
            'status' => 'processing',
            'job_id' => $jobId,
        ]);
    }

    /**
     * Download the exported file
     */
    public function downloadExport(string $jobId): BinaryFileResponse|JsonResponse
    {
        $filePath = "exports/transactions/{$jobId}.xlsx";

        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json([
                'error' => 'Export file not found. It may have expired.',
            ], 404);
        }

        $fileName = "transactions-" . date('Y-m-d', strtotime(pathinfo($filePath, PATHINFO_FILENAME))) . ".xlsx";

        $absolutePath = Storage::disk('local')->path($filePath);

        return response()->download($absolutePath, $fileName);
    }

    /**
     * Clean up old export files (run via scheduled command)
     */
    public static function cleanupOldExports(): void
    {
        $exportsPath = 'exports/transactions';
        $files = Storage::disk('local')->files($exportsPath);

        foreach ($files as $file) {
            $fileTime = Storage::disk('local')->lastModified($file);
            $now = time();

            // Delete files older than 24 hours
            if (($now - $fileTime) > 86400) {
                Storage::disk('local')->delete($file);
            }
        }
    }
    // End of Export Services
}
