<?php

namespace App\Services;

use App\Models\City;
use App\Repositories\CityRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class CityService
{
    public function __construct(
        private readonly CityRepository $cityRepository,
    ) {}

    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        /*
            filters
                - search: keyword search
                - perPage: by default 15
        */
        return $this->cityRepository->paginate($filters, $perPage);
    }

    public function findOrFail(string $id): City
    {
        return $this->cityRepository->findOrFail($id);
    }

    public function create(array $data): City
    {
        return $this->cityRepository->create($data);
    }

    public function update(string $id, array $data): City
    {
        $city = $this->cityRepository->findOrFail($id);
        return $this->cityRepository->update($city, $data);
    }

    public function delete(string $id): void
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
