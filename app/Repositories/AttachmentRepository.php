<?php

namespace App\Repositories;

use App\Models\Attachment;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AttachmentRepository
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

    public function createTransactionAttachment(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data): Transaction {
            $transaction->details()->createMany($data);

            return $transaction;
        });
    }

    public function createTransactionDetailAttachment(TransactionDetail $transactionDetail, array $data): TransactionDetail
    {
        return DB::transaction(function () use ($transactionDetail, $data): TransactionDetail {
            $transactionDetail->details()->createMany($data);

            return $transactionDetail;
        });
    }

    public function update(Attachment $attachment, array $data): Attachment
    {
        $attachment->update($data);

        return $attachment->refresh();
    }

    public function updateStatus(Attachment $attachment, string $status): Attachment
    {
        $attachment->update(['status' => $status]);

        return $attachment->refresh();
    }

    public function delete(Attachment $attachment): void
    {
        $attachment->update(['deleted_at' => now()]);
    }
}
