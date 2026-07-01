<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\StoreDriverRequest;
use App\Http\Requests\Driver\UpdateDriverRequest;

use App\Http\Resources\DriverResource;
use App\Services\DriverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DriverController extends Controller
{
    public function __construct(private readonly DriverService $driverService) {}


    public function index(Request $request): JsonResponse
    {
        /* params
            per_page - int
            search - string
            deleted - boolean true / false
        */
        $perPage = (int) ($request->query('per_page', 15) ?? 15);
        $data = $this->driverService->list($request->only(['search', 'deleted', 'id', 'is_active']), $perPage);
        return response()->json($data);
    }

    public function store(StoreDriverRequest $request): DriverResource
    {
        return new DriverResource(
            $this->driverService->create($request->validated())
        );
    }

    public function show(string $driverId): DriverResource
    {
        return new DriverResource(
            $this->driverService->findOrFail($driverId)
        );
    }

    public function update(UpdateDriverRequest $request, string $driverId): DriverResource
    {
        return new DriverResource(
            $this->driverService->update($driverId, $request->validated())
        );
    }

    public function destroy(string $driverId): JsonResponse
    {
        $this->driverService->delete($driverId);
        return response()->json(['message' => 'Deleted.']);
    }
}
