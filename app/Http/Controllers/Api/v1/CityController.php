<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\City\StoreCityRequest;
use App\Http\Requests\City\UpdateCityRequest;
use App\Http\Resources\CityResource;
use App\Models\City;
use App\Services\CityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CityController extends Controller
{
    public function __construct(private readonly CityService $cityService) {}

    public function index(Request $request): JsonResponse
    {
        /* params
            per_page - int
            search - string
            deleted - boolean true / false
        */
        $perPage = (int) ($request->query('per_page', 15) ?? 15);
        $data = $this->cityService->list($request->only(['search', 'deleted']), $perPage);
        return response()->json($data);
    }

    public function store(StoreCityRequest $request): CityResource
    {
        return new CityResource(
            $this->cityService->create($request->validated())
        );
    }

    public function show(string $city): CityResource
    {
        return new CityResource(
            $this->cityService->findOrFail($city)
        );
    }

    public function update(UpdateCityRequest $request, string $city): CityResource
    {
        return new CityResource(
            $this->cityService->update($city, $request->validated())
        );
    }

    public function destroy(string $city): JsonResponse
    {
        $this->cityService->delete($city);
        return response()->json(['message' => 'Deleted.']);
    }
}
