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
        // $role_admin = Role::create(['name' => 'Super Admin']);
        // $staff = Role::create(['name' => 'Staff']);

        // User::create([
        //     'name'     => 'Admin User',
        //     'email'    => 'admin@logistics.com',
        //     'password' => Hash::make('password123'),
        //     'role_id' => $role_admin->id
        // ]);

        // User::create([
        //     'name'     => 'Staff',
        //     'email'    => 'staff@logistics.com',
        //     'password' => Hash::make('password123'),
        //     'role_id' => $staff->id
        // ]);

        $this->call([
            // DistrictSeeder::class,
            // SubDistrictSeeder::class,
            VehicleSeeder::class,
            CustomerSeeder::class,
            BankAccountSeeder::class
        ]);
    }
}





