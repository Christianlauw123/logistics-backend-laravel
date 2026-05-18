<?php

namespace App\Services;

use App\Models\SubDistrict;
use App\Repositories\SubDistrictRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class SubDistrictService
{
    public function __construct(
        private readonly SubDistrictRepository $subDistrictRepository,
    ) {}

    public function list(?int $districtId = null): Collection
    {
        return $districtId
            ? $this->subDistrictRepository->allByDistrict($districtId)
            : $this->subDistrictRepository->all();
    }

    public function findOrFail(int $id): SubDistrict
    {
        return $this->subDistrictRepository->findOrFail($id);
    }

    public function create(array $data): SubDistrict
    {
        return $this->subDistrictRepository->create($data);
    }

    public function update(int $id, array $data): SubDistrict
    {
        $subDistrict = $this->subDistrictRepository->findOrFail($id);
        return $this->subDistrictRepository->update($subDistrict, $data);
    }

    public function delete(int $id): void
    {
        $subDistrict = $this->subDistrictRepository->findOrFail($id);

        // if (
        //     $subDistrict->originTripPrices()->exists() ||
        //     $subDistrict->destinationTripPrices()->exists()
        // ) {
        //     throw ValidationException::withMessages([
        //         'sub_district' => 'Cannot delete a sub-district that is used in trip prices.',
        //     ]);
        // }

        $this->subDistrictRepository->delete($subDistrict);
    }
}
