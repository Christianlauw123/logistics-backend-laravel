<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        /*
            filters
                - search: keyword search
                - perPage: by default 15
        */
        return Customer::query()
            ->when(
                isset($filters['search']),
                fn ($q) => $q->where('name', 'ilike', "%{$filters['search']}%")
            )
            ->when(
                isset($filters['deleted']),
                fn ($q) => $q->withTrashed()
            )
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString(); // keeps filters in pagination links
    }

    public function findOrFail(string $id): Customer
    {
        return Customer::findOrFail($id);
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
