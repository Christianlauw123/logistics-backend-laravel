<?php

namespace App\Services;

use App\Models\Attachment;
use App\Repositories\AttachmentRepository;
use Illuminate\Pagination\LengthAwarePaginator;
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
        $details = $data['details'];

        $transactionData = collect($data)
            ->except('details')
            ->merge(['user_id' => $userId])
            ->toArray();

        return $this->attachmentRepository->create($data);
    }

    public function update(string $id, array $data): Attachment
    {
        $attachment = $this->attachmentRepository->findByIdOrFail($id);

        // Business rule: only DRAFT Attachments can be edited
        if ($attachment->status !== 'DRAFT') {
            throw ValidationException::withMessages([
                'status' => 'Only DRAFT Attachments can be edited.',
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

        // Business rule: only DRAFT can be deleted
        if ($attachment->status !== 'SUBMITTED') {
            throw ValidationException::withMessages([
                'status' => 'Only DRAFT Attachments can be deleted.',
            ]);
        }

        $this->attachmentRepository->delete($attachment);
    }
}
