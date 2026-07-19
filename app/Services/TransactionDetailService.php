<?php

namespace App\Services;

use App\Enums\TransactionDetails\TransactionDetailStatus;
use App\Enums\Transactions\TransactionStatus;
use App\Models\TransactionDetail;
use App\Repositories\TransactionDetailRepository;
use App\Repositories\TransactionRepository;
use App\Services\ExternalServices\GoogleDriveService;
use App\Services\ExternalServices\TelegramService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class TransactionDetailService
{
    public function __construct(
        private readonly TransactionDetailRepository $transactionDetailRepository,
        private readonly TransactionRepository $transactionRepository,
        private readonly GoogleDriveService $googleDriveService,
        private readonly AttachmentService $attachmentService,
        private readonly TelegramService $telegramService
    ) {}

    public function findOrFail(string $id): TransactionDetail
    {
        return $this->transactionDetailRepository->findByIdOrFail($id);
    }

    public function create(array $data): TransactionDetail
    {
        DB::beginTransaction();
        try{
            $transaction = $this->transactionRepository->findByIdOrFail($data['transaction_id']);
            // Prevent Update Detail if Parent not in
            if(!in_array($transaction->status, TransactionStatus::allowUpdates(), true)){
                throw ValidationException::withMessages([
                    'amount' => 'Status bukan SUBMITTED',
                ]);
            }

            $this->preventClaimTabunganModified('create', $data['purpose']);

            $transactionDetailData = collect($data)->except('file')->toArray();

            // Check all submitted if added with this one is exceed or not
            if ($transactionDetailData['is_special_case'] == false){
                $detailCreationAllowed = $this->transactionRepository->preCalculateCurrentTransactionTotal($transactionDetailData['transaction_id'], $transactionDetailData['amount']);
                if(!$detailCreationAllowed['state']){
                    throw ValidationException::withMessages([
                        'amount' => 'Jumlah nominal Baru maksimal '.$detailCreationAllowed['current_total_discrepancy'],
                    ]);
                }
            }

            $transactionDetail = $this->transactionDetailRepository->create($transactionDetailData);

            $transactionDetail->refresh();

            $chosenNumber = null;
            $activeNumbers = TransactionDetail::whereIn('status', TransactionDetailStatus::requestedNumberInUseDefaults())->pluck('amount_unique_number')->toArray();
            $allAllowedNumbers = range(1, 9);
            $remainingNumbers = collect($allAllowedNumbers)->diff($activeNumbers);

            if (count($remainingNumbers) > 0)
                $chosenNumber = $remainingNumbers->first();

            if ($chosenNumber === null)
                throw ValidationException::withMessages([
                    'amount_unique_number' => 'Tidak dapat create detail, uniq number full',
                ]);

            try{
                $this->transactionDetailRepository->prePopulateCreateTransactionDetail($transactionDetail->id, $chosenNumber);
            }catch (QueryException $e) {
                if ($e->getCode() == 23000){
                    throw ValidationException::withMessages([
                        'amount_unique_number' => 'Tidak dapat create detail, uniq number full',
                    ]);
                }
                throw $e;
            }

            // Upload the file
            if (!empty($data['file']))
                $this->attachmentService->create(['file' => $data['file'], 'transaction_detail_id' => $transactionDetail->id]);

            DB::commit();

            // Send Pengajuan ke Telegram Bot
            $this->telegramService->telegramSendRequestDetail($transactionDetail->id);

            return $transactionDetail->refresh();
        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $id, array $data): TransactionDetail
    {
        DB::beginTransaction();
        try{
            $transactionDetail = $this->transactionDetailRepository->findByIdOrFail($id);

            // Business rule: only SUBMITTED transactions can be edited
            if (request()->user()->role->name !== 'Super Admin'){
                if ($transactionDetail->status !== TransactionDetailStatus::SUBMITTED) {
                    throw ValidationException::withMessages([
                        'status' => 'Hanya SUBMITTED detail yang dapat dirubah',
                    ]);
                }

                if(!in_array($transactionDetail->transaction->status, TransactionStatus::allowUpdates(), true)){
                    throw ValidationException::withMessages([
                        'amount' => 'Status Transaksi bukan SUBMITTED',
                    ]);
                }
            }


            $detailCreationAllowed = $this->transactionRepository->preCalculateCurrentTransactionTotal($transactionDetail->transaction->id, -$transactionDetail->amount + $data['amount']);
            if(!$detailCreationAllowed['state']){
                throw ValidationException::withMessages([
                    'amount' => 'Jumlah nominal Baru maksimal '.$detailCreationAllowed['current_total_discrepancy'],
                ]);
            }

            $transactionDetailData = collect($data)->except('file')->toArray();
            $transactionDetail = $this->transactionDetailRepository->update($transactionDetail, $transactionDetailData);

            // If file exists, re-upload the file, delete the old one
            if (!empty($data['file'])){
                if($transactionDetail->attachment?->file_id)
                    $this->googleDriveService->delete($transactionDetail->attachment->file_id);
                $this->attachmentService->create(['file' => $data['file'], 'transaction_detail_id' => $transactionDetail->id]);
            }

            $this->updateTransactionStatusIfAllDetailsDone($transactionDetail->id);

            DB::commit();
            return $transactionDetail->refresh();
        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function changeStatus(string $id, string $status): TransactionDetail
    {
        DB::beginTransaction();
        try{
            $transactionDetail = $this->transactionDetailRepository->findByIdOrFail($id);

            $newStatus = TransactionDetailStatus::tryFrom($status);

            if (!$newStatus) {
                throw ValidationException::withMessages([
                    'status' => "Status tidak valid.",
                ]);
            }

            if (request()->user()->role->name !== 'Super Admin'){
                if(!in_array($transactionDetail->transaction->status, TransactionStatus::allowUpdates(), true)){
                    throw ValidationException::withMessages([
                        'amount' => 'Status Transaksi bukan SUBMITTED',
                    ]);
                }

                if (! $transactionDetail->status->canTransitionTo($newStatus)) {
                    throw ValidationException::withMessages([
                        'status' => "Gagal Update dari {$transactionDetail->status} ke {$newStatus}.",
                    ]);
                }
            }

            $transactionDetail = $this->transactionDetailRepository->updateStatus($transactionDetail, $status);
            $this->firstPaymentDateIsSet($transactionDetail->id);
            $this->updateTransactionStatusIfAllDetailsDone($transactionDetail->id);

            DB::commit();
            return $transactionDetail->refresh();
        }catch(Throwable $e){
            DB::rollBack();
            throw ValidationException::withMessages([
                'status' => "Gagal update status",
            ]);
        }
    }

    public function delete(string $id): void
    {
        DB::beginTransaction();
        try{
            $transactionDetail = $this->transactionDetailRepository->findByIdOrFail($id);
            $this->preventClaimTabunganModified('delete', $transactionDetail->purpose);

            if (request()->user()->role->name !== 'Super Admin'){
                if ($transactionDetail->status !== TransactionDetailStatus::SUBMITTED) {
                    throw ValidationException::withMessages([
                        'status' => 'Hanya SUBMITTED detail yang dapat dihapus',
                    ]);
                }
            }
            if($transactionDetail->attachment?->file_id)
                $this->googleDriveService->delete($transactionDetail->attachment->file_id);

            $this->transactionDetailRepository->delete($transactionDetail);
            DB::commit();
        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }

    // Custom Function
    // Function for Updating the Transaction Detail Status to DONE, and also update the Transaction Status to DONE_AND_WAITING_DOCUMENT if all UJP is filled
    public function updateDetailBasedOnOutsider(string $transactionDetailId, float $amount): JsonResponse
    {
        DB::beginTransaction();
        try{
            // Check if the transaction amount is -1, returning not found sent to telegram
            $transactionDetail = $this->transactionDetailRepository->findByIdOrFail($transactionDetailId);
            if($transactionDetail->status !== TransactionDetailStatus::SUBMITTED){
                $this->telegramService->telegramSendMessageFeedback(
                    false,
                    'Transaksi tidak ditemukan atau sudah diproses https://logistics-fe.n8n-learning.web.id/transactions/'.$transactionDetail->transaction->id,
                    $transactionDetail->transaction->id
                );
                return response()->json([
                    'id' => $transactionDetail->transaction->id,
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan atau sudah diproses https://logistics-fe.n8n-learning.web.id/transactions/'.$transactionDetail->transaction->id,
                ], 400);
            }
            if ($amount !== ($transactionDetail->amount + ($transactionDetail->amount_unique_number ?? 0))) {
                $this->telegramService->telegramSendMessageFeedback(
                    false,
                    "Transaksi dengan jumlah ".$amount." tidak ditemukan https://logistics-fe.n8n-learning.web.id/transactions/".$transactionDetail->transaction->id,
                    $transactionDetail->transaction->id
                );

                return response()->json([
                    'id' => $transactionDetail->id,
                    'success' => false,
                    'message' => 'Transaksi dengan jumlah '.$amount.' tidak ditemukan https://logistics-fe.n8n-learning.web.id/transactions/'.$transactionDetail->transaction->id,
                ], 400);
            }

            $this->changeStatus($transactionDetailId, TransactionDetailStatus::DONE->value);
            $this->firstPaymentDateIsSet($transactionDetail->id);
            $this->updateTransactionStatusIfAllDetailsDone($transactionDetail->id);
            DB::commit();
            return response()->json([
                'id' => $transactionDetail->id,
                'success' => true,
                'message' => 'Berhasil update status',
            ], 200);

        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }

    }

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

    private function updateTransactionStatusIfAllDetailsDone(string $transactionDetailId): void
    {
        $transactionDetail = $this->transactionDetailRepository->findByIdOrFail($transactionDetailId);
        // Check the end of transaction if all ujp is filled, then go to the done_waiting document
        $transactionState = $this->transactionRepository->getCurrentTotal($transactionDetail->transaction_id);

        $status = TransactionStatus::DONE_AND_WAITING_DOCUMENT->value;
        if($transactionState['total'] < $transactionState['trip_price_amount']){
            $status = TransactionStatus::SUBMITTED->value;
        }

        $this->transactionRepository->updateStatus($transactionDetail->transaction, $status);

    }

    private function firstPaymentDateIsSet(string $transactionDetailId): void
    {
        $transactionDetail = $this->transactionDetailRepository->findByIdOrFail($transactionDetailId);

         // Set First Payment Date on Transaciton as DO Date
        if ($transactionDetail->status === TransactionDetailStatus::DONE && !$transactionDetail->transaction->is_set_first_payment_date) {
            $this->transactionRepository->setFirstPaymentDate($transactionDetail->transaction, $transactionDetail->created_at);
        }
    }


}
