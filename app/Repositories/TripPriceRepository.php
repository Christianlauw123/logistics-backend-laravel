<?php


namespace App\Repositories;

use App\Models\TripPrice;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TripPriceRepository
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        /*
            filters
                - search: keyword search customers.name, originSubDistrict.name. destinationSubDistrict.city name properties
                - customerId: specific customerId
                - perPage: by default 15
        */
        return TripPrice::query()
            ->with(['customer', 'originSubDistrict', 'destinationSubDistrict'])
            ->when(
                isset($filters['search']),
                fn ($q) => $q->where(function ($query) use ($filters) {
                    $query->where('customers.name', 'ilike', "%{$filters['search']}%")
                          ->orWhereRelation('originSubDistrict', 'name', 'ilike', "%{$filters['search']}%")
                          ->orWhereRelation('destinationSubDistrict', 'name', 'ilike', "%{$filters['search']}%");
                })
            )
            ->when(
                isset($filters['customerId']),
                fn ($q) => $q->where('id', $filters['customerId'])
            )
            ->when(
                isset($filters['deleted']),
                fn ($q) => $q->withTrashed()
            )
            ->orderBy('trip_prices.base_price')
            ->paginate($perPage)
            ->withQueryString(); // keeps filters in pagination links
    }

    public function allByCustomer(string $customerId): Collection
    {
        return TripPrice::with(['originSubDistrict', 'destinationSubDistrict'])
            ->where('customer_id', $customerId)
            ->get();
    }

    public function findOrFail(string $id): TripPrice
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
