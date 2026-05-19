<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        /* params
            per_page - int
            search - string
            deleted - boolean true / false
        */
        return User::query()
            ->when(
                isset($filters['search']),
                fn ($q) => $q->where(function ($query) use ($filters) {
                    $query->where('users.name', 'ilike', "%{$filters['search']}%")
                          ->orWhere('users.email', 'ilike', "%{$filters['search']}%");
                })
            )
            ->when(
                isset($filters['deleted']),
                fn ($q) => $q->withTrashed()
            )
            ->orderBy('trip_prices.base_price')
            ->paginate($perPage)
            ->withQueryString(); // keeps filters in pagination links
    }

    public function findOrFail(string $id): User
    {
        return User::with('role')->findOrFail($id);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->refresh()->load('role');
    }

    public function delete(User $user): void
    {
        $user->update(['deleted_at' => now()]);
    }
}
