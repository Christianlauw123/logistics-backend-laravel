<?php

namespace App\Repositories;

use App\Models\District;
use Illuminate\Database\Eloquent\Collection;

class DistrictRepository
{
    public function all(): Collection
    {
        return District::with('city')->orderBy('name')->get();
    }

    public function allByCity(int $cityId): Collection
    {
        return District::where('city_id', $cityId)->orderBy('name')->get();
    }

    public function findOrFail(int $id): District
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
