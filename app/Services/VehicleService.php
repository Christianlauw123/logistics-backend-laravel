<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Repositories\VehicleRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class VehicleService
{
    public function __construct(
        private readonly VehicleRepository $vehicleRepository,
    ) {}

    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->vehicleRepository->paginate($filters, $perPage);
    }

    public function findOrFail(string $id): Vehicle
    {
        return $this->vehicleRepository->findOrFail($id);
    }

    public function create(array $data): Vehicle
    {
        return $this->vehicleRepository->create($data);
    }

    public function update(string $id, array $data): Vehicle
    {
        $vehicle = $this->vehicleRepository->findOrFail($id);
        if ($vehicle->transactions()->exists()) {
            throw ValidationException::withMessages([
                'vehicle' => 'Cannot update a vehicle that has transactions.',
            ]);
        }
        return $this->vehicleRepository->update($vehicle, $data);
    }

    public function delete(string $id): void
    {
        $vehicle = $this->vehicleRepository->findOrFail($id);
        $this->vehicleRepository->delete($vehicle);
    }

    // // Soft deactivate instead of hard delete — vehicle may have transaction history
    // public function deactivate(string $id): Vehicle
    // {
    //     $vehicle = $this->vehicleRepository->findOrFail($id);
    //     return $this->vehicleRepository->update($vehicle, ['is_active' => false]);
    // }
}
