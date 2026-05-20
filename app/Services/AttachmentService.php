<?php

namespace App\Services;

use App\Services\ExternalServices\GoogleDriveService;
use App\Models\Attachment;
use App\Repositories\AttachmentRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class AttachmentService
{
    public function __construct(
        private readonly AttachmentRepository $attachmentRepository,
    ) {}

    public function findOrFail(string $id): Attachment
    {
        return $this->attachmentRepository->findByIdOrFail($id);
    }

    public function create(array $data, string $userId): Attachment
    {
        $subFolder = '';
        if (isset($data['transaction_id']))
            $subFolder = "transactions/{$data['transaction_id']}";
        if (isset($data['transaction_detail_id']))
            $subFolder = "transaction_details/{$data['transaction_detail_id']}";

        $transactionData = collect($data)
            ->merge([
                'user_id' => $userId,
            ])
            ->toArray();

        return $this->attachmentRepository->create($transactionData);
    }

    public function update(string $id, array $data): Attachment
    {
        $attachment = $this->attachmentRepository->findByIdOrFail($id);

        // Business rule: only PENDING Attachments can be edited
        if ($attachment->status !== 'PENDING') {
            throw ValidationException::withMessages([
                'status' => 'Only PENDING Attachments can be edited.',
            ]);
        }

        return $this->attachmentRepository->update($attachment, $data);
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
        $attachment = $this->attachmentRepository->findByIdOrFail($id);

        // Business rule: only SUBMITTED can be deleted
        if ($attachment->status !== 'SUBMITTED') {
            throw ValidationException::withMessages([
                'status' => 'Only SUBMITTED Attachments can be deleted.',
            ]);
        }

        $this->attachmentRepository->delete($attachment);
    }
}
