<?php

namespace App\Services;

use App\Models\Driver;
use App\Repositories\DriverRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DriverService
{
    public function __construct(
        private readonly DriverRepository $driverRepository,
    ) {}

    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        /*
            filters
                - search: keyword search
                - perPage: by default 15
        */
        return $this->driverRepository->paginate($filters, $perPage);
    }

    public function findOrFail(string $id): Driver
    {
        return $this->driverRepository->findOrFail($id);
    }

    public function create(array $data): Driver
    {
        return $this->driverRepository->create($data);
    }

    public function update(string $id, array $data): Driver
    {
        $driver = $this->driverRepository->findOrFail($id);
        return $this->driverRepository->update($driver, $data);
    }

    public function delete(string $id): void
    {
        $driver = $this->driverRepository->findOrFail($id);
        $this->driverRepository->delete($driver);
    }
}
