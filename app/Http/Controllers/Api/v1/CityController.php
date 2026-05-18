<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\City\StoreCityRequest;
use App\Http\Requests\City\UpdateCityRequest;
use App\Services\CityService;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    public function __construct(private readonly CityService $cityService) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->cityService->list()]);
    }

    public function store(StoreCityRequest $request): JsonResponse
    {
        return response()->json(
            ['data' => $this->cityService->create($request->validated())],
            201
        );
    }

    public function show(int $city): JsonResponse
    {
        return response()->json(['data' => $this->cityService->findOrFail($city)]);
    }

    public function update(UpdateCityRequest $request, int $city): JsonResponse
    {
        return response()->json(
            ['data' => $this->cityService->update($city, $request->validated())]
        );
    }

    public function destroy(int $city): JsonResponse
    {
        $this->cityService->delete($city);
        return response()->json(['message' => 'Deleted.']);
    }
}
