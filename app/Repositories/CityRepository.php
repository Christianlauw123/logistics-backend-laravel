<?php

namespace App\Repositories;

use App\Models\City;
use Illuminate\Database\Eloquent\Collection;

class CityRepository
{
    public function all(): Collection
    {
        return City::orderBy('name')->get();
    }

    public function findOrFail(int $id): City
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
