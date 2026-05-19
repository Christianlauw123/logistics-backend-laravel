<?php

namespace App\Services;

use App\Models\District;
use App\Repositories\DistrictRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DistrictService
{
    public function __construct(
        private readonly DistrictRepository $districtRepository,
    ) {}

    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->districtRepository->paginate($filters, $perPage);
    }

    public function findOrFail(string $id): District
    {
        return $this->districtRepository->findOrFail($id);
    }

    public function create(array $data): District
    {
        return $this->districtRepository->create($data);
    }

    public function update(string $id, array $data): District
    {
        $district = $this->districtRepository->findOrFail($id);
        return $this->districtRepository->update($district, $data);
    }

    public function delete(string $id): void
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
