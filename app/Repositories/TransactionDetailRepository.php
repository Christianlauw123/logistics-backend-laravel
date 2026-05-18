<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransactionDetailRepository
{
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

    public function create(Transaction $transaction, array $details): TransactionDetail
    {
        $newlyCreatedDetail = $transaction->details()->createMany($details);
        return $newlyCreatedDetail;
    }

    public function update(TransactionDetail $transactionDetail, array $data): TransactionDetail
    {
        $transactionDetail->update($data);

        return $transactionDetail->refresh();
    }

    public function updateStatus(TransactionDetail $transactionDetail, string $status): TransactionDetail
    {
        $transactionDetail->update(['status' => $status]);

        return $transactionDetail->refresh();
    }

    public function delete(TransactionDetail $transactionDetail): void
    {
        $transactionDetail->update(['deleted_at' => now()]);
    }
}
