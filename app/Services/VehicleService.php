<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Repositories\VehicleRepository;
use Illuminate\Database\Eloquent\Collection;

class VehicleService
{
    public function __construct(
        private readonly VehicleRepository $vehicleRepository,
    ) {}

    public function list(bool $activeOnly = false): Collection
    {
        return $this->vehicleRepository->all($activeOnly);
    }

    public function findOrFail(int $id): Vehicle
    {
        return $this->vehicleRepository->findOrFail($id);
    }

    public function create(array $data): Vehicle
    {
        return $this->vehicleRepository->create($data);
    }

    public function update(int $id, array $data): Vehicle
    {
        $vehicle = $this->vehicleRepository->findOrFail($id);
        return $this->vehicleRepository->update($vehicle, $data);
    }

    public function delete(int $id): void
    {
        $vehicle = $this->vehicleRepository->findOrFail($id);
        $this->vehicleRepository->delete($vehicle);
    }

    // // Soft deactivate instead of hard delete — vehicle may have transaction history
    // public function deactivate(int $id): Vehicle
    // {
    //     $vehicle = $this->vehicleRepository->findOrFail($id);
    //     return $this->vehicleRepository->update($vehicle, ['is_active' => false]);
    // }
}
