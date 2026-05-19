<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Http\Requests\TripPrice\StoreTripPriceRequest;
use App\Http\Requests\TripPrice\UpdateTripPriceRequest;
use App\Http\Resources\TripPriceResource;
use App\Services\TripPriceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripPriceController extends Controller
{
    public function __construct(private readonly TripPriceService $tripPriceService) {}

    public function index(Request $request): JsonResponse
    {
        /* params
            per_page - int
            search - string
            customerId - uuid
            isActive - boolean
            deleted - boolean true / false
        */
        $perPage = (int) ($request->query('per_page', 15) ?? 15);

        $data = $this->tripPriceService->list($request->only(['search', 'customerId', 'isActive', 'deleted']), $perPage);
        return response()->json($data);
    }

    public function store(StoreTripPriceRequest $request): TripPriceResource
    {
        return new TripPriceResource(
            $this->tripPriceService->create($request->validated())
        );
    }

    public function show(string $tripPriceId): TripPriceResource
    {
        return new TripPriceResource(
            $this->tripPriceService->findOrFail($tripPriceId)
        );
    }

    public function update(UpdateTripPriceRequest $request, string $tripPriceId): TripPriceResource
    {
        return new TripPriceResource(
            $this->tripPriceService->update($tripPriceId, $request->validated())
        );
    }

    public function destroy(string $tripPriceId): JsonResponse
    {
        $this->tripPriceService->delete($tripPriceId);
        return response()->json(['message' => 'Deleted.']);
    }
}
