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
                - search: keyword search until city properties
                - cityId: specific cityId
                - perPage: by default 15
        */
        return District::query()
            ->with('city')
            ->when(
                isset($filters['search']),
                fn ($q) => $q->where(function ($query) use ($filters) {
                    $query->where('name', 'ilike', "%{$filters['search']}%")
                        ->orWhereRelation('city', 'name', 'ilike', "%{$filters['search']}%");
                })
            )
            ->when(
                isset($filters['cityId']),
                fn ($q) => $q->where('id', $filters['cityId'])
            )
            ->when(
                isset($filters['deleted']),
                fn ($q) => $q->withTrashed()
            )
            ->orderBy('districts.name')
            ->paginate($perPage)
            ->withQueryString(); // keeps filters in pagination links
    }

    public function findOrFail(string $id): District
    {
        return District::with(['city', 'subDistricts'])->findOrFail($id);
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
