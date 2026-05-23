<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class RoleRepository
{
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        /* params
            per_page - int
            search - string
            deleted - boolean true / false
        */
        return Role::query()
            ->when(
                isset($filters['search']),
                fn ($q) => $q->where(function ($query) use ($filters) {
                    $query->where('roles.name', 'ilike', "%{$filters['search']}%");
                })
            )
            ->when(
                isset($filters['deleted']) && $filters['deleted']==true,
                fn ($q) => $q->withTrashed()
            )
            ->select('id', 'name')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString(); // keeps filters in pagination links
    }

    // public function findOrFail(string $id): User
    // {
    //     return User::with('role')->findOrFail($id);
    // }

    // public function create(array $data): User
    // {
    //     return User::create($data);
    // }

    // public function update(User $user, array $data): User
    // {
    //     $user->update($data);
    //     return $user->refresh();
    // }

    // public function delete(User $user): void
    // {
    //     $user->update(['deleted_at' => now()]);
    // }
}
