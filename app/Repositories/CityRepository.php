<?php

namespace App\Repositories;

use App\Models\City;
use Illuminate\Pagination\LengthAwarePaginator;

class CityRepository
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        /*
            filters
                - search: keyword search
                - perPage: by default 15
        */
        return City::query()
            ->when(
                isset($filters['search']),
                fn ($q) => $q->where('name', 'ilike', "%{$filters['search']}%")
            )
            ->when(
                isset($filters['deleted']) && $filters['deleted']==true,
                fn ($q) => $q->withTrashed()
            )
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString(); // keeps filters in pagination links
    }

    public function findOrFail(string $id): City
    {
        return City::with('districts')->findOrFail($id);
    }

    public function create(array $data): City
    {
        return City::create($data);
    }

    public function update(City $city, array $data): City
    {
        $city->update($data);
        return $city->refresh();
    }

    public function delete(City $city): void
    {
        $city->update(['deleted_at' => now()]);
    }
}
