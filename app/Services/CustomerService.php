<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepository $customerRepository,
    ) {}

    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->customerRepository->paginate($filters, $perPage);
    }

    public function findOrFail(string $id): Customer
    {
        return $this->customerRepository->findOrFail($id);
    }

    public function create(array $data): Customer
    {
        return $this->customerRepository->create($data);
    }

    public function update(string $id, array $data): Customer
    {
        $customer = $this->customerRepository->findOrFail($id);
        return $this->customerRepository->update($customer, $data);
    }

    public function delete(string $id): void
    {
        $customer = $this->customerRepository->findOrFail($id);

        // if ($customer->transactions()->exists()) {
        //     throw ValidationException::withMessages([
        //         'customer' => 'Cannot delete a customer that has transactions.',
        //     ]);
        // }

        $this->customerRepository->delete($customer);
    }
}
