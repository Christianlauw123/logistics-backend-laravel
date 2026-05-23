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
                - search: keyword search customers.name, originSubDistrict.name. destinationSubDistrict name properties
                - customerId: specific customerId
                - perPage: by default 15
        */
        return TripPrice::query()
            ->with([
                'customer:id,name',
                'originSubDistrict:id,name,district_id',
                'originSubDistrict.district:id,name',
                'destinationSubDistrict:id,name,district_id',
                'destinationSubDistrict.district:id,name'
            ])
            ->when(
                isset($filters['search']),
                fn ($q) => $q->where(function ($query) use ($filters) {
                    $query->whereRelation('customer', 'name', 'ilike', "%{$filters['search']}%")
                          ->orWhereRelation('originSubDistrict', 'name', 'ilike', "%{$filters['search']}%")
                          ->orWhereRelation('destinationSubDistrict', 'name', 'ilike', "%{$filters['search']}%");
                })
            )
            ->when(
                isset($filters['customerId']),
                fn ($q) => $q->where('customer_id', $filters['customerId'])
            )

            ->when(
                isset($filters['origin_sub_district_id']),
                fn ($q) => $q->where('origin_sub_district_id', $filters['origin_sub_district_id'])
            )

            ->when(
                isset($filters['dest_sub_district_id']),
                fn ($q) => $q->where('dest_sub_district_id', $filters['dest_sub_district_id'])
            )
            ->when(
                isset($filters['deleted']) && $filters['deleted']==true,
                fn ($q) => $q->withTrashed()
            )
            ->orderBy('trip_prices.base_price')
            ->select('id', 'base_price', 'created_at', 'customer_id', 'origin_sub_district_id', 'dest_sub_district_id')
            ->paginate($perPage)
            ->withQueryString(); // keeps filters in pagination links
    }

    public function findOrFail(string $id): TripPrice
    {
        return TripPrice::with([
            'customer',
            'originSubDistrict.district',
            'destinationSubDistrict.district',
        ])->findOrFail($id);
    }

    public function create(array $data): TripPrice
    {
        return TripPrice::create($data);
    }

    public function update(TripPrice $tripPrice, array $data): TripPrice
    {
        $tripPrice->update($data);
        return $tripPrice->refresh();
    }

    public function delete(TripPrice $tripPrice): void
    {
        $tripPrice->delete();
    }
}
