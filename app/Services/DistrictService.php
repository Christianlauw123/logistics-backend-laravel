<?php

namespace App\Services;

use App\Models\District;
use App\Repositories\DistrictRepository;
use Illuminate\Database\Eloquent\Collection;

class DistrictService
{
    public function __construct(
        private readonly DistrictRepository $districtRepository,
    ) {}

    public function list(?int $cityId = null): Collection
    {
        return $cityId
            ? $this->districtRepository->allByCity($cityId)
            : $this->districtRepository->all();
    }

    public function findOrFail(int $id): District
    {
        return $this->districtRepository->findOrFail($id);
    }

    public function create(array $data): District
    {
        return $this->districtRepository->create($data);
    }

    public function update(int $id, array $data): District
    {
        $district = $this->districtRepository->findOrFail($id);
        return $this->districtRepository->update($district, $data);
    }

    public function delete(int $id): void
    {
        $district = $this->districtRepository->findOrFail($id);

        // if ($district->subDistricts()->exists()) {
        //     throw ValidationException::withMessages([
        //         'district' => 'Cannot delete a district that has sub-districts.',
        //     ]);
        // }

        $this->districtRepository->delete($district);
    }
}
