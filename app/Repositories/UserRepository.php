<?php

namespace App\Repositories;
use App\Models\User;

class UserRepository{
    public function createUserWithRole(array $data, string $roleId): User{
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role_id' => $roleId
        ]);
    }

    public function updateUserWithRole(string $id, array $data, ?string $roleId): User{
        $updateUser = [];
        isset($data['name']) ? $updateUser['name'] = $data['name'] : [];
        isset($data['email']) ? $updateUser['email'] = $data['email'] : [];
        isset($roleId) ? $updateUser['role_id'] = $roleId: [];

        return User::findOrFail($id)->update($updateUser);
    }


    public function findByEmail(string $email): ?User{
        return User::where('email', $email)->first();
    }
}