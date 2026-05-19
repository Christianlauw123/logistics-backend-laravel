<?php

namespace App\Services;

use App\Models\SubDistrict;
use App\Repositories\SubDistrictRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class SubDistrictService
{
    public function __construct(
        private readonly SubDistrictRepository $subDistrictRepository,
    ) {}

    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->subDistrictRepository->paginate($filters, $perPage);
    }

    public function findOrFail(string $id): SubDistrict
    {
        return $this->subDistrictRepository->findOrFail($id);
    }

    public function create(array $data): SubDistrict
    {
        return $this->subDistrictRepository->create($data);
    }

    public function update(string $id, array $data): SubDistrict
    {
        $subDistrict = $this->subDistrictRepository->findOrFail($id);
        return $this->subDistrictRepository->update($subDistrict, $data);
    }

    public function delete(string $id): void
    {
        $subDistrict = $this->subDistrictRepository->findOrFail($id);

        // if (
        //     $subDistrict->origstringripPrices()->exists() ||
        //     $subDistrict->destinationTripPrices()->exists()
        // ) {
        //     throw ValidationException::withMessages([
        //         'sub_district' => 'Cannot delete a sub-district that is used in trip prices.',
        //     ]);
        // }

        $this->subDistrictRepository->delete($subDistrict);
    }
}
