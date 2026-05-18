<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::create([
            'name'     => 'Admin User'
        ]);

        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@logistics.com',
            'password' => Hash::make('password123'),
            'role_id' => $role->id
        ]);
    }
}
