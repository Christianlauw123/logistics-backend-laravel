<?php

namespace App\Http\Controllers\Api\V1;

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
        */
        $perPage = (int) ($request->query('per_page', 15) || 15);
        $data = $this->vehicleService->list($request->only(['search', 'deleted']), $perPage);
        return response()->json($data);
    }

    public function store(StoreVehicleRequest $request): VehicleResource
    {
        return new VehicleResource(
            $this->vehicleService->create($request->validated())
        );
    }

    public function show(string $vehicle): VehicleResource
    {
        return new VehicleResource(
            $this->vehicleService->findOrFail($vehicle)
        );
    }

    public function update(UpdateVehicleRequest $request, string $vehicle): VehicleResource
    {
        return new VehicleResource(
            $this->vehicleService->update($vehicle, $request->validated())
        );
    }

    public function destroy(string $vehicle): JsonResponse
    {
        $this->vehicleService->delete($vehicle);
        return response()->json(['message' => 'Deleted.']);
    }
}
