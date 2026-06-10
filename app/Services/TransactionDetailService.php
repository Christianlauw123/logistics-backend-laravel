<?php

namespace App\Services;

use App\Models\TransactionDetail;
use App\Repositories\TransactionDetailRepository;
use App\Repositories\TransactionRepository;
use App\Services\ExternalServices\GoogleDriveService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class TransactionDetailService
{
    public function __construct(
        private readonly TransactionDetailRepository $transactionDetailRepository,
        private readonly TransactionRepository $transactionRepository,
        private readonly GoogleDriveService $googleDriveService,
    ) {}

    public function findOrFail(string $id): TransactionDetail
    {
        return $this->transactionDetailRepository->findByIdOrFail($id);
    }

    public function create(array $data, string $userId): TransactionDetail
    {
        $transaction = $this->transactionRepository->findByIdOrFail($data['transaction_id']);

        if(in_array($transaction->status, ['DONE', 'CANCELLED', 'REJECTED'], true)){
            throw ValidationException::withMessages([
                'amount' => 'Status bukan SUBMITTED / APPROVED',
            ]);
        }

        $this->preventClaimTabunganModified('create', $data['purpose']);

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
                'status' => 'Hanya SUBMITTED detail yang dapat dirubah',
            ]);
        }

        if(in_array($transactionDetail->transaction->status, ['DONE', 'CANCELLED', 'REJECTED'], true)){
            throw ValidationException::withMessages([
                'amount' => 'Status Transaksi bukan SUBMITTED / APPROVED',
            ]);
        }

        $detailCreationAllowed = $this->transactionRepository->preCalculateCurrentTransactionTotal($transactionDetail->transaction->id, -$transactionDetail->amount + $data['amount']);
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

        if (in_array($transactionDetail->transaction->status, ['DONE', 'CANCELLED', 'REJECTED'], true)) {
            throw ValidationException::withMessages([
                'status' => "Gagal Update. Status Transaksi telah: {$transactionDetail->transaction->status}.",
            ]);
        }

        if (! in_array($status, $allowedTransitions[$current], true)) {
            throw ValidationException::withMessages([
                'status' => "Gagal Update dari {$current} ke {$status}.",
            ]);
        }

        return $this->transactionDetailRepository->updateStatus($transactionDetail, $status);
    }

    public function delete(string $id): void
    {
        $transactionDetail = $this->transactionDetailRepository->findByIdOrFail($id);
        $this->preventClaimTabunganModified('delete', $transactionDetail->purpose);

        if ($transactionDetail->status !== 'SUBMITTED') {
            throw ValidationException::withMessages([
                'status' => 'Hanya SUBMITTED detail yang dapat dihapus',
            ]);
        }
        if($transactionDetail->attachment->file_id)
            $this->googleDriveService->delete($transactionDetail->attachment->file_id);

        $this->transactionDetailRepository->delete($transactionDetail);
    }

    // Custom Function
    // Prevent claim & tabungan deleted or modified
    private function preventClaimTabunganModified(string $state, string $purpose): void{
        $purposeCheckFound = match(str($purpose)->trim()->lower()){
            'claim' => 'claim',
            'tabungan' => 'tabungan',
            'klaim' => 'klaim',
            default => ''
        };
        if (empty($purposeCheckFound)){
            $message = match($state){
                'create' => 'ditambah',
                'update' => 'dirubah',
                'delete' => 'dihapus',
                'default' => ''
            };
            throw ValidationException::withMessages([
                'status' => 'Claim & Tabungan tidak dapat '.$message,
            ]);
        }
    }
}
