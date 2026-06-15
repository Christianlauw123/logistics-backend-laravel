<?php

namespace App\Services;

use App\Enums\TransactionDetails\TransactionDetailStatus;
use App\Enums\Transactions\TransactionStatus;
use App\Models\TransactionDetail;
use App\Repositories\TransactionDetailRepository;
use App\Repositories\TransactionRepository;
use App\Services\ExternalServices\GoogleDriveService;
use Illuminate\Validation\ValidationException;

class TransactionDetailService
{
    public function __construct(
        private readonly TransactionDetailRepository $transactionDetailRepository,
        private readonly TransactionRepository $transactionRepository,
        private readonly GoogleDriveService $googleDriveService,
        private readonly AttachmentService $attachmentService,
    ) {}

    public function findOrFail(string $id): TransactionDetail
    {
        return $this->transactionDetailRepository->findByIdOrFail($id);
    }

    public function create(array $data): TransactionDetail
    {
        $transaction = $this->transactionRepository->findByIdOrFail($data['transaction_id']);

        // Prevent Update Detail if Parent not in
        if(!in_array($transaction->status, TransactionStatus::allowUpdates(), true)){
            throw ValidationException::withMessages([
                'amount' => 'Status bukan SUBMITTED / APPROVED',
            ]);
        }

        $this->preventClaimTabunganModified('create', $data['purpose']);

        $transactionDetailData = collect($data)
            ->except('file')
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

        // Upload the file
        if (!empty($data['file']))
            $this->attachmentService->create([
                'file' => $data['file'],
                'transaction_detail_id' => $transactionDetail->id
            ]);

        return $transactionDetail->refresh();
    }

    public function update(string $id, array $data): TransactionDetail
    {
        $transactionDetail = $this->transactionDetailRepository->findByIdOrFail($id);

        // Business rule: only SUBMITTED transactions can be edited
        if ($transactionDetail->status !== TransactionDetailStatus::SUBMITTED) {
            throw ValidationException::withMessages([
                'status' => 'Hanya SUBMITTED detail yang dapat dirubah',
            ]);
        }

        if(!in_array($transactionDetail->transaction->status, TransactionStatus::allowUpdates(), true)){
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

        $transactionDetailData = collect($data)->except('file')->toArray();
        $transactionDetail = $this->transactionDetailRepository->update($transactionDetail, $transactionDetailData);
        // If file exists, re-upload the file, delete the old one
        if (!empty($data['file'])){
            if($transactionDetail->attachment->file_id)
                $this->googleDriveService->delete($transactionDetail->attachment->file_id);
            $this->attachmentService->create([
                'file' => $data['file'],
                'transaction_detail_id' => $transactionDetail->id
            ]);
        }
            

        return $transactionDetail->refresh();
    }

    public function changeStatus(string $id, string $status): TransactionDetail
    {
        $transactionDetail = $this->transactionDetailRepository->findByIdOrFail($id);

        $newStatus = TransactionDetailStatus::tryFrom($status);
        if (!$newStatus) {
            throw ValidationException::withMessages([
                'status' => "Status tidak valid.",
            ]);
        }

        if(!in_array($transactionDetail->transaction->status, TransactionStatus::allowUpdates(), true)){
            throw ValidationException::withMessages([
                'amount' => 'Status Transaksi bukan SUBMITTED / APPROVED',
            ]);
        }

        if (! $transactionDetail->status->canTransitionTo($newStatus)) {
            throw ValidationException::withMessages([
                'status' => "Gagal Update dari {$transactionDetail->status} ke {$newStatus}.",
            ]);
        }

        return $this->transactionDetailRepository->updateStatus($transactionDetail, $status);
    }

    public function delete(string $id): void
    {
        $transactionDetail = $this->transactionDetailRepository->findByIdOrFail($id);
        $this->preventClaimTabunganModified('delete', $transactionDetail->purpose);

        if ($transactionDetail->status !== TransactionDetailStatus::SUBMITTED) {
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
        if (!empty($purposeCheckFound)){
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
