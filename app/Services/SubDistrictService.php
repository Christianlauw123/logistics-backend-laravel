<?php

namespace App\Services;

use App\Models\SubDistrict;
use App\Repositories\SubDistrictRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class SubDistrictService
{
    public function __construct(
        private readonly SubDistrictRepository $subDistrictRepository,
    ) {}

    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->subDistrictRepository->paginate($filters, $perPage);
    }

    public function findOrFail(string $id): SubDistrict
    {
        return $this->subDistrictRepository->findOrFail($id);
    }

    public function create(array $data): SubDistrict
    {
        DB::beginTransaction();
        try{
            $exists = SubDistrict::where('district_id', $data['district_id'])
                ->where('name', $data['name'])
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'name' => 'Sub district already exists in this district.',
                ]);
            }
            $subDistrict = $this->subDistrictRepository->create($data);
            DB::commit();
            return $subDistrict->refresh();
        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $id, array $data): SubDistrict
    {
        DB::beginTransaction();
        try{
            $subDistrict = $this->subDistrictRepository->findOrFail($id);
            $this->subDistrictRepository->update($subDistrict, $data);
            DB::commit();
            return $subDistrict->refresh();
        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $id): void
    {
        DB::beginTransaction();
        try{
            $subDistrict = $this->subDistrictRepository->findOrFail($id);
            $this->subDistrictRepository->delete($subDistrict);
            DB::commit();
        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }
}
