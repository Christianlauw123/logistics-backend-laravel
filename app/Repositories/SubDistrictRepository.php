<?php

namespace App\Repositories;

use App\Models\SubDistrict;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SubDistrictRepository
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        /*
            filters
                - search: keyword search sub_districts.name, district.name properties
                - districtId: specific districtId
                - perPage: by default 15
        */
        return SubDistrict::query()
            ->with([
                'district:id,name'
            ])
            ->when(
                isset($filters['search']),
                fn ($q) => $q->where(function ($query) use ($filters) {
                    $query->where('sub_districts.name', 'ilike', "%{$filters['search']}%")
                          ->orWhereRelation('district', 'name', 'ilike', "%{$filters['search']}%");
                })
            )
            ->when(
                isset($filters['districtId']),
                fn ($q) => $q->where('id', $filters['districtId'])
            )
            ->when(
                isset($filters['deleted']) && $filters['deleted']==true,
                fn ($q) => $q->withTrashed()
            )
            ->orderBy('sub_districts.name')
            ->select('id', 'name', 'created_at', 'district_id')
            ->paginate($perPage)
            ->withQueryString(); // keeps filters in pagination links
    }

    public function findOrFail(string $id): SubDistrict
    {
        return SubDistrict::with('district')->findOrFail($id);
    }

    public function create(array $data): SubDistrict
    {
        return SubDistrict::create($data);
    }

    public function update(SubDistrict $subDistrict, array $data): SubDistrict
    {
        $subDistrict->update($data);
        return $subDistrict->refresh();
    }

    public function delete(SubDistrict $subDistrict): void
    {
        $subDistrict->update(['deleted_at' => now()]);
    }
}
