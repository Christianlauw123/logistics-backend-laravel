<?php

namespace App\Services;

use App\Enums\Attachments\AttachmentStatus;
use App\Enums\Attachments\AttachmentUploadStatus;
use App\Services\ExternalServices\GoogleDriveService;
use App\Models\Attachment;
use App\Repositories\AttachmentRepository;
use App\Repositories\TransactionDetailRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Throwable;

class AttachmentService
{
    public function __construct(
        private readonly AttachmentRepository $attachmentRepository,
        private readonly GoogleDriveService $googleDriveService,
        private readonly TransactionRepository $transactionRepostory,
        private readonly TransactionDetailRepository $transactionDetailRepostory
    ) {}

    public function findOrFail(string $id): Attachment
    {
        return $this->attachmentRepository->findByIdOrFail($id);
    }

    public function create(array $data): Attachment
    {
        DB::beginTransaction();
        try{
            $folderId = '';
            $filename = '';
            if (isset($data['transaction_id'])){
                $folderId = $this->getFolder($data['transaction_id'], 'transaction');
                $filename = $data['file']->getClientOriginalName()."_".Str::random(10)."_TEMP_{$data['transaction_id']}";
                $folderId = $folderId['folder_id'];
            }

            if (isset($data['transaction_detail_id'])){
                $folderId = $this->getFolder($data['transaction_detail_id'], 'transaction_detail');
                $filename = $data['file']->getClientOriginalName()."_".Str::random(10)."_TEMP_{$data['transaction_detail_id']}";
                $folderId = $folderId['sub_folder_id'];
            }

            $driveData = $this->googleDriveService->upload(
                $data['file'],
                folderId: $folderId,
                filename: $filename,
            );

            $transactionData = collect($data)
                ->merge([
                    'original_file_name' => $data['file']->getClientOriginalName(),
                    'transaction_detail_id' => $data['transaction_detail_id'] ?? null,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'file_url' => $driveData['file_url'],
                    'file_provider' => 'google-drive',
                    'file_id' => $driveData['drive_file_id'],
                    'upload_status' => AttachmentUploadStatus::COMPLETED,
                    'uploaded_at' => now()
                ])
                ->toArray();

            $attachment = $this->attachmentRepository->create($transactionData);
            DB::commit();
            return $attachment->refresh();
        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $id, array $data): Attachment
    {
        DB::beginTransaction();
        try{
            $attachment = $this->attachmentRepository->findByIdOrFail($id);

            // Business rule: only PENDING Attachments can be edited
            if ($attachment->status !== AttachmentStatus::PENDING) {
                throw ValidationException::withMessages([
                    'status' => 'Only PENDING Attachments can be edited.',
                ]);
            }
            $this->attachmentRepository->update($attachment, $data);
            DB::commit();
            return $attachment->refresh();
        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function changeStatus(string $id, string $status): Attachment
    {
        $attachment = $this->attachmentRepository->findByIdOrFail($id);

        // Business rule: status must follow order
        $allowedTransitions = [
            'PENDING'   => ['VERIFIED', 'REJECTED'],
            'VERIFIED' => [],
            'REJECTED'     => [],
        ];

        $current = $attachment->status;

        if (! in_array($status, $allowedTransitions[$current], true)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition from {$current} to {$status}.",
            ]);
        }

        return $this->attachmentRepository->updateStatus($attachment, $status);
    }

    public function delete(string $id): void
    {
        DB::beginTransaction();
        try{
            $attachment = $this->attachmentRepository->findByIdOrFail($id);

            // Delete From Drive
            // Upload into same folder, but deleted folder
            if($attachment->file_id)
                $this->googleDriveService->delete($attachment->file_id);
            $this->attachmentRepository->delete($attachment);
            DB::commit();
        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }

    private function getFolder(string $id, string $type): array {
        $transaction = null;
        if($type === 'transaction'){
            $transaction = $this->transactionRepostory->findByIdOrFail($id);
        }elseif($type === 'transaction_detail'){
            $transactionDetail = $this->transactionDetailRepostory->findByIdOrFail($id);
            $transaction = $transactionDetail->transaction;
        }else{
            throw ValidationException::withMessages([
                'transaction' => 'Attachment not valid',
            ]);
        }
        return [
            'folder_id' => $transaction->file_folder_id,
            'sub_folder_id' => $transaction->file_sub_folder_id,
        ];
    }
}
