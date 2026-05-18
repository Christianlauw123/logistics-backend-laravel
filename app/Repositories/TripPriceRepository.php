<?php


namespace App\Repositories;

use App\Models\TripPrice;
use Illuminate\Database\Eloquent\Collection;

class TripPriceRepository
{
    public function allByCustomer(int $customerId): Collection
    {
        return TripPrice::with(['originSubDistrict', 'destinationSubDistrict'])
            ->where('customer_id', $customerId)
            ->get();
    }

    public function findOrFail(int $id): TripPrice
    {
        return TripPrice::with([
            'customer',
            'originSubDistrict.district.city',
            'destinationSubDistrict.district.city',
        ])->findOrFail($id);
    }

    public function create(array $data): TripPrice
    {
        return TripPrice::create($data);
    }

    public function update(TripPrice $tripPrice, array $data): TripPrice
    {
        $tripPrice->update($data);
        return $tripPrice->refresh()->load([
            'originSubDistrict',
            'destinationSubDistrict',
        ]);
    }

    public function delete(TripPrice $tripPrice): void
    {
        $tripPrice->update(['deleted_at' => now()]);
    }
}
