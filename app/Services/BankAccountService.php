<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Repositories\BankAccountRepository;
use Illuminate\Database\Eloquent\Collection;

class BankAccountService
{
    public function __construct(
        private readonly BankAccountRepository $bankAccountRepository,
    ) {}

    public function listByCustomer(int $customerId): Collection
    {
        return $this->bankAccountRepository->allByCustomer($customerId);
    }

    public function findOrFail(int $id): BankAccount
    {
        return $this->bankAccountRepository->findOrFail($id);
    }

    public function create(array $data): BankAccount
    {
        return $this->bankAccountRepository->create($data);
    }

    public function update(int $id, array $data): BankAccount
    {
        $bankAccount = $this->bankAccountRepository->findOrFail($id);
        return $this->bankAccountRepository->update($bankAccount, $data);
    }

    public function delete(int $id): void
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
