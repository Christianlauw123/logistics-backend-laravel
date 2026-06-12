<?php

namespace App\Services;

use App\Models\TripPrice;
use App\Repositories\TripPriceRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class TripPriceService
{
    public function __construct(
        private readonly TripPriceRepository $tripPriceRepository,
    ) {}

    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->tripPriceRepository->paginate($filters, $perPage);
    }

    public function listByCustomer(string $customerId): LengthAwarePaginator
    {
        return $this->tripPriceRepository->paginate(['customer_id' => $customerId]);
    }

    public function findOrFail(string $id): TripPrice
    {
        return $this->tripPriceRepository->findOrFail($id);
    }

    public function create(array $data): TripPrice
    {
        DB::beginTransaction();
        try{
            // Business rule: no duplicate origin-destination for same customer
            $filters = [
                'customer_id' => $data['customer_id'],
                'origin_sub_district_id' => $data['origin_sub_district_id'],
                'dest_sub_district_id' => $data['dest_sub_district_id']
            ];
            $tripPriceCheck = $this->tripPriceRepository->paginate($filters);

            if (count($tripPriceCheck->items()) > 0) {
                throw ValidationException::withMessages([
                    'trip_price' => 'A price for this customer and route already exists.',
                ]);
            }
            $tripPrice = $this->tripPriceRepository->create($data);
            DB::commit();
            return $tripPrice->refresh();
        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }

    }

    public function update(string $id, array $data): TripPrice
    {
        DB::beginTransaction();
        try{
            $tripPrice = $this->tripPriceRepository->findOrFail($id);
            // if ($tripPrice->transactions()->exists()) {
            //     throw ValidationException::withMessages([
            //         'trip_price' => 'Cannot update a trip price that has transactions.',
            //     ]);
            // }
            $this->tripPriceRepository->update($tripPrice, $data);
            DB::commit();
            return $tripPrice->refresh();
        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $id): void
    {
        DB::beginTransaction();
        try{
            $tripPrice = $this->tripPriceRepository->findOrFail($id);
            $this->tripPriceRepository->delete($tripPrice);
            DB::commit();
        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * List allowed sub-districts for a given customer and optional origin sub-district.
     * Returning destination sub-districts if origin is provided, otherwise returning origin sub-districts.
     */
    public function listTripPriceSubDistricts(array $filters): Collection
    {
        $originSubDistricts = null;
        $results = [];

        $originId = $filters['origin_sub_district_id'] ?? null;

        $originSubDistricts = $this->tripPriceRepository->paginate($filters);

        $results = $originSubDistricts->through(function ($item) use ($originId) {
            $data = null;
            if ($originId != null)
                $data = $item->destinationSubDistrict;
            else
                $data = $item->originSubDistrict;

            return [
                'id'   => $data->id,
                'name' => $data->name,
                'district' => [
                    'id' => $data->district->id,
                    'name' => $data->district->name,
                ]
            ];
        });

        // Get the underlying collection, make it unique by 'id', and reset array keys
        $uniqueData = $results->getCollection()->unique('id')->values();
        return Collection::make(['data' => $uniqueData]);
    }
}
