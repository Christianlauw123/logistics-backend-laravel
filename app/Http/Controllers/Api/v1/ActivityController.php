<?php

namespace App\Http\Controllers\Api\v1;

use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class ActivityController extends Controller
{
    public function __construct(
        private readonly ActivityService $activityService,
    ) {}

    /**
     * GET /api/transactions/{id}/logs
     */
    public function transactionLogs(string $id): JsonResponse
    {
        $logs = $this->activityService->getTransactionHistory($id);

        return response()->json([
            'success' => true,
            'transaction_id' => $id,
            'count' => $logs['count'], // Handy reference of total log entries
            'data' => $logs['data']    // Returns a flat array of all entries
        ]);
    }
}
