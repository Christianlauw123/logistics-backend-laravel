<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Repositories\BankAccountRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

class BankAccountService
{
    public function __construct(
        private readonly BankAccountRepository $bankAccountRepository,
    ) {}

    public function list(array $filters, int $perPage): LengthAwarePaginator
    {
        /*
            filters
                - search: keyword search
                - perPage: by default 15
        */
        return $this->bankAccountRepository->paginate($filters, $perPage);
    }

    public function findOrFail(string $id): BankAccount
    {
        return $this->bankAccountRepository->findOrFail($id);
    }

    public function create(array $data): BankAccount
    {
        DB::beginTransaction();
        try{
            $bankAccount = $this->bankAccountRepository->create($data);
            DB::commit();
            return $bankAccount->refresh();
        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function update(string $id, array $data): BankAccount
    {
        DB::beginTransaction();
        try{
            $bankAccount = $this->bankAccountRepository->findOrFail($id);
            $this->bankAccountRepository->update($bankAccount, $data);
            DB::commit();
            return $bankAccount->refresh();
        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(string $id): void
    {
        DB::beginTransaction();
        try{
            $bankAccount = $this->bankAccountRepository->findOrFail($id);
            $this->bankAccountRepository->delete($bankAccount);
            DB::commit();
        }catch(Throwable $e){
            DB::rollBack();
            throw $e;
        }

        // if ($bankAccount->transactions()->exists()) {
        //     throw ValidationException::withMessages([
        //         'bank_account' => 'Cannot delete a bank account that has transactions.',
        //     ]);
        // }

    }
}
