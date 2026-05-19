<?php

namespace App\Http\Controllers\Api\V1;

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

    public function show(string $customer): CustomerResource
    {
        return new CustomerResource(
            $this->customerService->findOrFail($customer)
        );
    }

    public function update(UpdateCustomerRequest $request, string $customer): CustomerResource
    {
        return new CustomerResource(
            $this->customerService->update($customer, $request->validated())
        );
    }

    public function destroy(string $customer): JsonResponse
    {
        $this->customerService->delete($customer);
        return response()->json(['message' => 'Deleted.']);
    }
}
