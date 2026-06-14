<?php

namespace App\Repositories;

use App\Enums\TransactionDetails\TransactionDetailStatus;
use App\Models\TransactionDetail;

class TransactionDetailRepository
{
    public function findById(string $id): ?TransactionDetail
    {
        return TransactionDetail::with([
            'transaction',
            'attachment',
        ])->find($id);
    }

    public function findByIdOrFail(string $id): TransactionDetail
    {
        return TransactionDetail::with([
            'transaction',
            'attachment',
        ])->findOrFail($id);
    }

    public function create(array $data): TransactionDetail
    {
        return TransactionDetail::create($data);
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
        $transactionDetail->delete();
    }

    public function prePopulateCreateTransactionDetail(string $transactionDetailId): void {
        $transactionDetail = $this->findByIdOrFail($transactionDetailId)->refresh();
        $transactionDetail->status = TransactionDetailStatus::SUBMITTED;
        $transactionDetail->save();
    }
}
