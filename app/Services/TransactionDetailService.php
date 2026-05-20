<?php

namespace App\Services;

use App\Models\TransactionDetail;
use App\Repositories\TransactionDetailRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class TransactionDetailService
{
    public function __construct(
        private readonly TransactionDetailRepository $transactionDetailRepository,
        private readonly TransactionRepository $transactionRepository,
    ) {}

    public function findOrFail(string $id): TransactionDetail
    {
        return $this->transactionDetailRepository->findByIdOrFail($id);
    }

    public function create(array $data, string $userId): TransactionDetail
    {
        $transactionDetailData = collect($data)
            ->merge(['user_id' => $userId])
            ->toArray();

        // Check all submitted if added with this one is exceed or not
        $detailCreationAllowed = $this->transactionRepository->preCalculateCurrentTransactionTotal($transactionDetailData['transaction_id'], $transactionDetailData['amount']);
        if(!$detailCreationAllowed['state']){
            throw ValidationException::withMessages([
                'amount' => 'Jumlah Amount Baru melebihi biaya trip maksimal '.$detailCreationAllowed['trip_price_amount'],
            ]);
        }
        $transactionDetail = $this->transactionDetailRepository->create($transactionDetailData);
        $this->transactionDetailRepository->prePopulateCreateTransactionDetail($transactionDetail->id);
        return $transactionDetail->refresh();
    }

    public function update(string $id, array $data): TransactionDetail
    {
        $transactionDetail = $this->transactionDetailRepository->findByIdOrFail($id);

        // Business rule: only SUBMITTED transactions can be edited
        if ($transactionDetail->status !== 'SUBMITTED') {
            throw ValidationException::withMessages([
                'status' => 'Only SUBMITTED transactions can be edited.',
            ]);
        }

        $detailCreationAllowed = $this->transactionRepository->preCalculateCurrentTransactionTotal($id, -$transactionDetail->amount + $data['amount']);
        if(!$detailCreationAllowed['state']){
            throw ValidationException::withMessages([
                'amount' => 'Jumlah Amount Baru melebihi biaya trip maksimal '.$detailCreationAllowed['trip_price_amount'],
            ]);
        }

        return $this->transactionDetailRepository->update($transactionDetail, $data);
    }

    public function changeStatus(string $id, string $status): TransactionDetail
    {
        $transactionDetail = $this->transactionDetailRepository->findByIdOrFail($id);

        // Business rule: status must follow order
        $allowedTransitions = [
            'SUBMITTED' => ['APPROVED', 'DONE', 'CANCELLED', 'REJECTED'],
            'APPROVED'  => ['DONE', 'CANCELLED', 'REJECTED'],
            'DONE'      => [],
            'CANCELLED' => [],
            'REJECTED'  => [],
        ];

        $current = $transactionDetail->status;

        if (! in_array($status, $allowedTransitions[$current], true)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition from {$current} to {$status}.",
            ]);
        }

        return $this->transactionDetailRepository->updateStatus($transactionDetail, $status);
    }

    public function delete(string $id): void
    {
        $transactionDetail = $this->transactionDetailRepository->findByIdOrFail($id);

        // Business rule: only SUBMITTED can be deleted
        if ($transactionDetail->status !== 'SUBMITTED') {
            throw ValidationException::withMessages([
                'status' => 'Only SUBMITTED transactions can be deleted.',
            ]);
        }

        $this->transactionDetailRepository->delete($transactionDetail);
    }
}
