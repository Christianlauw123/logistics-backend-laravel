<?php

namespace App\Repositories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

class VehicleRepository
{
    public function all(bool $activeOnly = false): Collection
    {
        return Vehicle::query()
            ->when($activeOnly, fn ($q) => $q->where('is_active', true))
            ->orderBy('plate_number')
            ->get();
    }

    public function findOrFail(int $id): Vehicle
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
        $vehicle->update(['deleted_at' => now()]);
    }
}
