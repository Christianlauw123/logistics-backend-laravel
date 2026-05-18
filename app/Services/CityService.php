<?php

namespace App\Services;

use App\Models\City;
use App\Repositories\CityRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class CityService
{
    public function __construct(
        private readonly CityRepository $cityRepository,
    ) {}

    public function list(): Collection
    {
        return $this->cityRepository->all();
    }

    public function findOrFail(int $id): City
    {
        return $this->cityRepository->findOrFail($id);
    }

    public function create(array $data): City
    {
        return $this->cityRepository->create($data);
    }

    public function update(int $id, array $data): City
    {
        $city = $this->cityRepository->findOrFail($id);
        return $this->cityRepository->update($city, $data);
    }

    public function delete(int $id): void
    {
        $city = $this->cityRepository->findOrFail($id);

        // // Business rule: cannot delete a city that has districts
        // if ($city->districts()->exists()) {
        //     throw ValidationException::withMessages([
        //         'city' => 'Cannot delete a city that has districts.',
        //     ]);
        // }

        $this->cityRepository->delete($city);
    }
}
