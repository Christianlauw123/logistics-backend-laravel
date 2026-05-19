<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Repositories\BankAccountRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

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
        return $this->bankAccountRepository->create($data);
    }

    public function update(string $id, array $data): BankAccount
    {
        $bankAccount = $this->bankAccountRepository->findOrFail($id);
        return $this->bankAccountRepository->update($bankAccount, $data);
    }

    public function delete(string $id): void
    {
        $bankAccount = $this->bankAccountRepository->findOrFail($id);

        // if ($bankAccount->transactions()->exists()) {
        //     throw ValidationException::withMessages([
        //         'bank_account' => 'Cannot delete a bank account that has transactions.',
        //     ]);
        // }

        $this->bankAccountRepository->delete($bankAccount);
    }
}
