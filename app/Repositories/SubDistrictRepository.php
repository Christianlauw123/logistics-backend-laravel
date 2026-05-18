<?php

namespace App\Repositories;

use App\Models\SubDistrict;
use Illuminate\Database\Eloquent\Collection;

class SubDistrictRepository
{
    public function all(): Collection
    {
        return SubDistrict::with('district.city')->orderBy('name')->get();
    }

    public function allByDistrict(int $districtId): Collection
    {
        return SubDistrict::where('district_id', $districtId)->orderBy('name')->get();
    }

    public function findOrFail(int $id): SubDistrict
    {
        return SubDistrict::with('district.city')->findOrFail($id);
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
