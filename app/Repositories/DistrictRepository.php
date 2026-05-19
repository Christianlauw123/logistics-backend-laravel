<?php

namespace App\Repositories;

use App\Models\District;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DistrictRepository
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        /*
            filters
                - search: keyword search
                - perPage: by default 15
        */
        return District::query()
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
            ->orderBy('districts.name')
            ->paginate($perPage)
            ->withQueryString(); // keeps filters in pagination links
    }

    public function findOrFail(string $id): District
    {
        return District::with('subDistricts')->findOrFail($id);
    }

    public function create(array $data): District
    {
        return District::create($data);
    }

    public function update(District $district, array $data): District
    {
        $district->update($data);
        return $district->refresh();
    }

    public function delete(District $district): void
    {
        $district->update(['deleted_at' => now()]);
    }
}
