<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use App\Http\Requests\Vehicle\StoreVehicleRequest;
use App\Http\Requests\Vehicle\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Services\VehicleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct(private readonly VehicleService $vehicleService) {}


    public function index(Request $request): JsonResponse
    {
        /* params
            per_page - int
            search - string
            deleted - boolean true / false
            is_active - boolean true / false - > default true
        */
        $perPage = (int) ($request->query('per_page', 15) ?? 15);
        $data = $this->vehicleService->list($request->only(['search', 'deleted', 'id', 'is_active']), $perPage);
        return response()->json($data);
    }

    public function store(StoreVehicleRequest $request): VehicleResource
    {
        return new VehicleResource(
            $this->vehicleService->create($request->validated())
        );
    }

    public function show(string $vehicleId): VehicleResource
    {
        return new VehicleResource(
            $this->vehicleService->findOrFail($vehicleId)
        );
    }

    public function update(UpdateVehicleRequest $request, string $vehicleId): VehicleResource
    {
        return new VehicleResource(
            $this->vehicleService->update($vehicleId, $request->validated())
        );
    }

    public function destroy(string $vehicleId): JsonResponse
    {
        $this->vehicleService->delete($vehicleId);
        return response()->json(['message' => 'Deleted.']);
    }
}
