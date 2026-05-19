<?php

namespace App\Services;

use App\Models\TransactionDetail;
use App\Repositories\TransactionDetailRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class TransactionDetailService
{
    public function __construct(
        private readonly TransactionDetailRepository $transactionDetailRepository,
    ) {}

    public function findOrFail(string $id): TransactionDetail
    {
        return $this->transactionDetailRepository->findByIdOrFail($id);
    }

    public function create(array $data, string $userId): TransactionDetail
    {
        $details = $data['details'];

        $transactionData = collect($data)
            ->except('details')
            ->merge(['user_id' => $userId])
            ->toArray();

        return $this->transactionDetailRepository->create($transactionData, $details);
    }

    public function update(string $id, array $data): TransactionDetail
    {
        $transaction = $this->transactionDetailRepository->findByIdOrFail($id);

        // Business rule: only DRAFT transactions can be edited
        if ($transaction->status !== 'DRAFT') {
            throw ValidationException::withMessages([
                'status' => 'Only DRAFT transactions can be edited.',
            ]);
        }

        return $this->transactionDetailRepository->update($transaction, $data);
    }

    public function changeStatus(string $id, string $status): TransactionDetail
    {
        $transaction = $this->transactionDetailRepository->findByIdOrFail($id);

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

        return $this->transactionDetailRepository->updateStatus($transaction, $status);
    }

    public function delete(string $id): void
    {
        $transactionDetail = $this->transactionDetailRepository->findByIdOrFail($id);

        // Business rule: only DRAFT can be deleted
        if ($transactionDetail->status !== 'SUBMITTED') {
            throw ValidationException::withMessages([
                'status' => 'Only DRAFT transactions can be deleted.',
            ]);
        }

        $this->transactionDetailRepository->delete($transactionDetail);
    }
}
