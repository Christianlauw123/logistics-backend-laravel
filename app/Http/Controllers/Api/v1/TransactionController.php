<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Http\Requests\Transaction\UpdateStatusRequest;
use App\Http\Resources\Transaction\TransactionResource;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService,
    ) {}

    /**
     * GET /api/v1/transactions
     * Optional filters: ?status=DRAFT&customer_id=1
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $transactions = $this->transactionService->list(
            $request->only(['status', 'customer_id'])
        );

        return TransactionResource::collection($transactions);
    }

    /**
     * POST /api/v1/transactions
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = $this->transactionService->create(
            $request->validated(),
            $request->user()->id,
        );

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/v1/transactions/{transaction}
     */
    public function show(int $transaction): TransactionResource
    {
        return new TransactionResource(
            $this->transactionService->findOrFail($transaction)
        );
    }

    /**
     * PUT /api/v1/transactions/{transaction}
     */
    public function update(UpdateTransactionRequest $request, int $transaction): TransactionResource
    {
        return new TransactionResource(
            $this->transactionService->update($transaction, $request->validated())
        );
    }

    /**
     * PATCH /api/v1/transactions/{transaction}/status
     */
    public function updateStatus(UpdateStatusRequest $request, int $transaction): TransactionResource
    {
        return new TransactionResource(
            $this->transactionService->changeStatus($transaction, $request->validated('status'))
        );
    }

    /**
     * DELETE /api/v1/transactions/{transaction}
     */
    public function destroy(int $transaction): JsonResponse
    {
        $this->transactionService->delete($transaction);

        return response()->json(['message' => 'Transaction deleted.']);
    }
}
