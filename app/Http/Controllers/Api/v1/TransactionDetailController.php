<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Http\Requests\TransactionDetail\StoreTransactionDetailRequest;
use App\Http\Requests\TransactionDetail\UpdateTransactionDetailRequest;
use App\Http\Requests\TransactionDetail\UpdateTransactionDetailStatusRequest;
use App\Http\Resources\TransactionDetailResource;

use App\Services\TransactionDetailService;
use Illuminate\Http\JsonResponse;


class TransactionDetailController extends Controller
{
    public function __construct(
        private readonly TransactionDetailService $transactionDetailService,
    ) {}

    /**
     * POST /api/v1/transaction_details
     */
    public function store(StoreTransactionDetailRequest $request): TransactionDetailResource
    {
        return new TransactionDetailResource(
            $this->transactionDetailService->create(
                $request->validated(),
                $request->user()?->id,
            )
        );
    }

    /**
     * GET /api/v1/transaction_details/{transaction}
     */
    public function show(string $transaction): TransactionDetailResource
    {
        return new TransactionDetailResource(
            $this->transactionDetailService->findOrFail($transaction)
        );
    }

    /**
     * PUT /api/v1/transaction_details/{transaction}
     */
    public function update(UpdateTransactionDetailRequest $request, string $transaction): TransactionDetailResource
    {
        return new TransactionDetailResource(
            $this->transactionDetailService->update($transaction, $request->validated())
        );
    }

    /**
     * PATCH /api/v1/transaction_details/{transaction}/status
     */
    public function updateStatus(UpdateTransactionDetailStatusRequest $request, string $transaction): TransactionDetailResource
    {
        return new TransactionDetailResource(
            $this->transactionDetailService->changeStatus($transaction, $request->validated('status'))
        );
    }

    /**
     * DELETE /api/v1/transaction_details/{transaction}
     */
    public function destroy(string $transaction): JsonResponse
    {
        $this->transactionDetailService->delete($transaction);

        return response()->json(['message' => 'Transaction deleted.']);
    }
}
