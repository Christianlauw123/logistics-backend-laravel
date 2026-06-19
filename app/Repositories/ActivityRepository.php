<?php

namespace App\Repositories;

use App\Models\Activity;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ActivityRepository
{
    public function getLogsByTransactionId(string $transactionId): Collection
    {
        return Activity::where('subject_type', Transaction::class)
            ->where('subject_id', $transactionId)
            ->with('causer')
            ->latest()
            ->get(); // Fetches the entire collection without pagination
    }

    public function getLogsTransactionDetailsByTransactionId(string $transactionId): Collection
    {
        $transcationDetails = TransactionDetail::where('transaction_id', $transactionId)->pluck('id')->toArray();
        return Activity::where('subject_type', TransactionDetail::class)
            ->where('subject_id', $transcationDetails)
            ->with('causer')
            ->latest()
            ->get(); // Fetches the entire collection without pagination
    }
}
