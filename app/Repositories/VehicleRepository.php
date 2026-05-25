<?php

namespace App\Repositories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class VehicleRepository
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        /* params
            per_page - int
            search - string
            deleted - boolean true / false
            is_active - boolean true / false - > default true
        */
        return Vehicle::query()
            ->when(
                isset($filters['search']),
                fn ($q) => $q->where(function ($query) use ($filters) {
                    $query->where('vehicles.plate_number', 'ilike', "%{$filters['search']}%")
                    ->orWhere('vehicles.name', 'ilike', "%{$filters['search']}%");
                })
            )
            ->when(
                isset($filters['id']),
                fn ($q) => $q->where('vehicles.id', $filters['id'])
            )
            ->when(
                isset($filters['deleted']) && $filters['deleted']==true,
                fn ($q) => $q->withTrashed()
            )
            ->when(
                isset($filters['is_active']),
                fn ($q) => $q->where('vehicles.is_active', $filters['is_active'] === 'true' ? true : false)
            )
            ->orderBy('vehicles.plate_number')
            ->paginate($perPage)
            ->withQueryString(); // keeps filters in pagination links
    }

    public function findOrFail(string $id): Vehicle
    {
        return Vehicle::findOrFail($id);
    }

    public function create(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle->update($data);
        return $vehicle->refresh();
    }

    public function delete(Vehicle $vehicle): void
    {
        $vehicle->delete();
    }
}
