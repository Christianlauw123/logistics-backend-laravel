<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::with('role')->orderBy('name')->paginate($perPage);
    }

    public function findOrFail(int $id): User
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
