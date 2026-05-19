<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
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
        $details = $data['details'];

        $transactionData = collect($data)
            ->except('details')
            ->merge(['user_id' => $userId])
            ->toArray();

        return $this->transactionRepository->create($transactionData, $details);
    }

    public function update(string $id, array $data): Transaction
    {
        $transaction = $this->transactionRepository->findByIdOrFail($id);

        // Business rule: only DRAFT transactions can be edited
        if ($transaction->status !== 'SUBMITTED') {
            throw ValidationException::withMessages([
                'status' => 'Only DRAFT transactions can be edited.',
            ]);
        }

        return $this->transactionRepository->update($transaction, $data);
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

        // Business rule: only DRAFT can be deleted
        if ($transaction->status !== 'SUBMITTED') {
            throw ValidationException::withMessages([
                'status' => 'Only DRAFT transactions can be deleted.',
            ]);
        }

        $this->transactionRepository->delete($transaction);
    }
}
