<?php

namespace App\Repositories;

use App\Models\BankAccount;
use Illuminate\Pagination\LengthAwarePaginator;

class BankAccountRepository
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        /*
            filters
                - search: keyword search
                - perPage: by default 15
        */
        return BankAccount::query()
            ->when(
                isset($filters['search']),
                fn ($q) => $q->where('bank_name', 'ilike', "%{$filters['search']}%")
                            ->orWhere('account_identifier_number', 'ilike', "%{$filters['search']}%")
                            ->orWhere('account_number', 'ilike', "%{$filters['search']}%")
                            ->orWhere('account_name', 'ilike', "%{$filters['search']}%")
            )
            ->when(
                isset($filters['id']),
                fn ($q) => $q->where('id', $filters['id'])
            )
            ->when(
                isset($filters['deleted']) && $filters['deleted']==true,
                fn ($q) => $q->withTrashed()
            )
            ->orderBy('account_identifier_number')
            ->select('id', 'bank_name', 'account_identifier_number', 'created_at')
            ->paginate($perPage)
            ->withQueryString(); // keeps filters in pagination links
    }

    public function findOrFail(string $id): BankAccount
    {
        return BankAccount::findOrFail($id);
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
