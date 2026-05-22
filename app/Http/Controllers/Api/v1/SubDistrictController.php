<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubDistrict\StoreSubDistrictRequest;
use App\Http\Requests\SubDistrict\UpdateSubDistrictRequest;
use App\Http\Resources\SubDistrictResource;
use App\Services\SubDistrictService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubDistrictController extends Controller
{
    public function __construct(private readonly SubDistrictService $subDistrictService) {}

    public function index(Request $request): JsonResponse
    {
        /* params
            per_page - int
            search - string
            districtId - uuid
            deleted - boolean true / false
        */
        $perPage = (int) ($request->query('per_page', 15) ?? 15);
        $data = $this->subDistrictService->list($request->only(['search', 'districtId', 'deleted', 'id']), $perPage);
        return response()->json($data);
    }

    public function store(StoreSubDistrictRequest $request): SubDistrictResource
    {
        return new SubDistrictResource(
            $this->subDistrictService->create($request->validated())
        );
    }

    public function show(string $subDistrictId): SubDistrictResource
    {
        return new SubDistrictResource(
            $this->subDistrictService->findOrFail($subDistrictId)
        );
    }

    public function update(UpdateSubDistrictRequest $request, string $subDistrictId): SubDistrictResource
    {
        return new SubDistrictResource(
            $this->subDistrictService->update($subDistrictId, $request->validated())
        );
    }

    public function destroy(string $subDistrictId): JsonResponse
    {
        $this->subDistrictService->delete($subDistrictId);
        return response()->json(['message' => 'Deleted.']);
    }
}
