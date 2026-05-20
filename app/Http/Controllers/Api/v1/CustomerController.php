<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;

use App\Http\Resources\CustomerResource;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customerService) {}


    public function index(Request $request): JsonResponse
    {
        /* params
            per_page - int
            search - string
            deleted - boolean true / false
        */
        $perPage = (int) ($request->query('per_page', 15) ?? 15);
        $data = $this->customerService->list($request->only(['search', 'deleted']), $perPage);
        return response()->json($data);
    }

    public function store(StoreCustomerRequest $request): CustomerResource
    {
        return new CustomerResource(
            $this->customerService->create($request->validated())
        );
    }

    public function show(string $customerId): CustomerResource
    {
        return new CustomerResource(
            $this->customerService->findOrFail($customerId)
        );
    }

    public function update(UpdateCustomerRequest $request, string $customerId): CustomerResource
    {
        return new CustomerResource(
            $this->customerService->update($customerId, $request->validated())
        );
    }

    public function destroy(string $customerId): JsonResponse
    {
        $this->customerService->delete($customerId);
        return response()->json(['message' => 'Deleted.']);
    }
}
