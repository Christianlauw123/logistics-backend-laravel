<?php

namespace App\Services;

use App\Models\TripPrice;
use App\Repositories\TripPriceRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class TripPriceService
{
    public function __construct(
        private readonly TripPriceRepository $tripPriceRepository,
    ) {}

    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->tripPriceRepository->paginate($filters, $perPage);
    }

    public function listByCustomer(string $customerId): Collection
    {
        return $this->tripPriceRepository->allByCustomer($customerId);
    }

    public function findOrFail(string $id): TripPrice
    {
        return $this->tripPriceRepository->findOrFail($id);
    }

    public function create(array $data): TripPrice
    {
        // Business rule: no duplicate origin-destination for same customer
        $exists = TripPrice::where('customer_id', $data['customer_id'])
            ->where('origin_sub_district_id', $data['origin_sub_district_id'])
            ->where('dest_sub_district_id', $data['dest_sub_district_id'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'trip_price' => 'A price for this customer and route already exists.',
            ]);
        }

        return $this->tripPriceRepository->create($data);
    }

    public function update(string $id, array $data): TripPrice
    {
        $tripPrice = $this->tripPriceRepository->findOrFail($id);
        return $this->tripPriceRepository->update($tripPrice, $data);
    }

    public function delete(string $id): void
    {
        $tripPrice = $this->tripPriceRepository->findOrFail($id);

        // if ($tripPrice->transactions()->exists()) {
        //     throw ValidationException::withMessages([
        //         'trip_price' => 'Cannot delete a trip price that has transactions.',
        //     ]);
        // }

        $this->tripPriceRepository->delete($tripPrice);
    }
}
