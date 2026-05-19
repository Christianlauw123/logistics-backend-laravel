<?php

namespace App\Services;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use App\Repositories\TripPriceRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly TripPriceRepository $tripPriceRepository,
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

        $this->transactionRepository->prePopulateTransaction($transaction->id);
        $this->transactionRepository->prePopulateCreateTransaction($transaction->id);
        return $transaction;
    }

    public function update(string $id, array $data): Transaction
    {
        $transaction = $this->transactionRepository->findByIdOrFail($id);

        // Business rule: only SUBMITTED transactions can be edited
        if ($transaction->status !== 'SUBMITTED') {
            throw ValidationException::withMessages([
                'status' => 'Only SUBMITTED transactions can be edited.',
            ]);
        }

        $this->transactionRepository->update($transaction, $data);
        $this->transactionRepository->prePopulateTransaction($transaction->id);
        return $transaction->refresh();
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
        if ($transaction->status !== 'SUBMITTED') {
            throw ValidationException::withMessages([
                'status' => 'Only SUBMITTED transactions can be deleted.',
            ]);
        }

        $this->transactionRepository->delete($transaction);
    }
}
