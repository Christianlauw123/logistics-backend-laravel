<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionStatusRequest;
use App\Http\Resources\TransactionResource;
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
     * Optional filters: ?status=SUBMITTED&customer_id=1
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search',
            'do_number',
            'status',
            'customer_id',
            'origin_sub_district_id',
            'dest_sub_district_id',
            'bank_account_id',
            'vehicle_id',
            'do_date_from',
            'do_date_to',
            'do_actual_date_from',
            'do_actual_date_to',
            'deleted'
        ]);

        $sort = [
            'by'        => $request->query('sort_by', 'created_at'),
            'direction' => $request->query('sort_dir', 'desc'),
        ];

        $perPage = (int) ($request->query('per_page', 15) ?? 15);

        $data = $this->transactionService->list($filters, $sort, $perPage);
        return response()->json($data);
    }

    /**
     * POST /api/v1/transactions
     */
    public function store(StoreTransactionRequest $request): TransactionResource
    {
        return new TransactionResource(
            $this->transactionService->create(
                $request->validated(),
                $request->user()->id,
            )
        );
    }

    /**
     * GET /api/v1/transactions/{transaction}
     */
    public function show(string $transactionId): TransactionResource
    {
        return new TransactionResource(
            $this->transactionService->findOrFail($transactionId)
        );
    }

    /**
     * PUT /api/v1/transactions/{transaction}
     */
    public function update(UpdateTransactionRequest $request, string $transactionId): TransactionResource
    {
        return new TransactionResource(
            $this->transactionService->update($transactionId, $request->validated())
        );
    }

    /**
     * PATCH /api/v1/transactions/{transaction}/status
     */
    public function updateStatus(UpdateTransactionStatusRequest $request, string $transactionId): TransactionResource
    {
        return new TransactionResource(
            $this->transactionService->changeStatus($transactionId, $request->validated('status'))
        );
    }

    /**
     * DELETE /api/v1/transactions/{transaction}
     */
    public function destroy(string $transactionId): JsonResponse
    {
        $this->transactionService->delete($transactionId);
        return response()->json(['message' => 'Transaction deleted.']);
    }
}
