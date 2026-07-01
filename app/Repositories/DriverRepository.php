<?php

namespace App\Repositories;

use App\Models\Driver;
use Illuminate\Pagination\LengthAwarePaginator;

class DriverRepository
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        /*
            filters
                - search: keyword search
                - perPage: by default 15
        */
        return Driver::query()
            ->when(
                isset($filters['id']),
                fn ($q) => $q->where('id', $filters['id'])
            )
            ->when(
                isset($filters['search']),
                fn ($q) => $q->where(function ($query) use ($filters) {
                    $query->where('name', 'ilike', "%{$filters['search']}%");
                })
            )
            ->when(
                isset($filters['deleted']) && $filters['deleted']==true,
                fn ($q) => $q->withTrashed()
            )
            ->when(
                isset($filters['is_active']),
                fn ($q) => $q->where('is_active', $filters['is_active'])
            )
            ->orderBy('name')
            ->select('id', 'name', 'created_at', 'is_active')
            ->paginate($perPage)
            ->withQueryString(); // keeps filters in pagination links
    }

    public function findOrFail(string $id): Driver
    {
        return Driver::findOrFail($id);
    }

    public function create(array $data): Driver
    {
        return Driver::create($data);
    }

    public function update(Driver $driver, array $data): Driver
    {
        $driver->update($data);
        return $driver->refresh();
    }

    public function delete(Driver $driver): void
    {
        $driver->delete();
    }
}
