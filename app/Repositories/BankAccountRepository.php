<?php

namespace App\Repositories;

use App\Models\BankAccount;
use Illuminate\Database\Eloquent\Collection;

class BankAccountRepository
{
    public function allByCustomer(int $customerId): Collection
    {
        return BankAccount::where('customer_id', $customerId)->get();
    }

    public function findOrFail(int $id): BankAccount
    {
        return BankAccount::with('customer')->findOrFail($id);
    }

    public function create(array $data): BankAccount
    {
        return BankAccount::create($data);
    }

    public function update(BankAccount $bankAccount, array $data): BankAccount
    {
        $bankAccount->update($data);
        return $bankAccount->refresh();
    }

    public function delete(BankAccount $bankAccount): void
    {
        $bankAccount->update(['deleted_at' => now()]);
    }
}
