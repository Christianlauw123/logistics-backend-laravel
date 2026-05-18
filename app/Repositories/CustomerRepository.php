<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Customer::with('city')->orderBy('name')->paginate($perPage);
    }

    public function findOrFail(int $id): Customer
    {
        return Customer::with(['city', 'bankAccounts'])->findOrFail($id);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);
        return $customer->refresh()->load('city');
    }

    public function delete(Customer $customer): void
    {
        $customer->update(['deleted_at' => now()]);
    }
}
