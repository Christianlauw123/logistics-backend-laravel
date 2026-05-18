<?php

namespace App\Repositories;

use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransactionRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Transaction::query()
            ->with(['customer', 'vehicle', 'user', 'details', 'attachments'])
            ->when(
                isset($filters['status']),
                fn ($q) => $q->where('status', $filters['status'])
            )
            ->when(
                isset($filters['customer_id']),
                fn ($q) => $q->where('customer_id', $filters['customer_id'])
            )
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Transaction
    {
        return Transaction::with([
            'customer',
            'tripPrice',
            'vehicle',
            'bankAccount',
            'user',
            'details',
            'attachments',
        ])->find($id);
    }

    public function findByIdOrFail(int $id): Transaction
    {
        return Transaction::with([
            'customer',
            'tripPrice',
            'vehicle',
            'bankAccount',
            'user',
            'details',
            'attachments',
        ])->findOrFail($id);
    }

    public function create(array $data): Transaction
    {
        $transaction = Transaction::create($data);
        return $transaction;
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);

        return $transaction->refresh();
    }

    public function updateStatus(Transaction $transaction, string $status): Transaction
    {
        $transaction->update(['status' => $status]);

        return $transaction->refresh();
    }

    public function delete(Transaction $transaction): void
    {
        $transaction->update(['deleted_at' => now()]);
    }
}
