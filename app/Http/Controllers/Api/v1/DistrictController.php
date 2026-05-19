<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\District\StoreDistrictRequest;
use App\Http\Requests\District\UpdateDistrictRequest;
use App\Http\Resources\DistrictResource;
use App\Services\DistrictService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DistrictController extends Controller
{
    public function __construct(private readonly DistrictService $districtService) {}

    public function index(Request $request): JsonResponse
    {
        /* params
            per_page - int
            search - string
            cityId - uuid
            deleted - boolean true / false
        */
        $perPage = (int) ($request->query('per_page', 15) || 15);
        $data = $this->districtService->list($request->only(['search', 'cityId', 'deleted']), $perPage);
        return response()->json($data);
    }

    public function store(StoreDistrictRequest $request): DistrictResource
    {
        return new DistrictResource(
            $this->districtService->create($request->validated())
        );
    }

    public function show(string $district): DistrictResource
    {
        return new DistrictResource(
            $this->districtService->findOrFail($district)
        );
    }

    public function update(UpdateDistrictRequest $request, string $district): DistrictResource
    {
        return new DistrictResource(
            $this->districtService->update($district, $request->validated())
        );
    }

    public function destroy(string $district): JsonResponse
    {
        $this->districtService->delete($district);
        return response()->json(['message' => 'Deleted.']);
    }
}
