<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Customer;
use App\Models\Role;
use App\Models\SubDistrict;
use App\Models\Transaction;
use App\Models\TripPrice;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // All can do
        $role_admin = Role::create(['name' => 'Super Admin']);
        // The one who can manage transactions
        // Create Request Trip Information, but not manage Do Number & Actual Date, Attachment
        $operational = Role::create(['name' => 'Operational']);
        // Manage Do Number & Actual Date, Attachment
        $staff = Role::create(['name' => 'Staff']);

        User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@logistics.com',
            'password' => Hash::make('password123'),
            'role_id' => $role_admin->id
        ]);

        User::create([
            'name'     => 'Staff',
            'email'    => 'staff@logistics.com',
            'password' => Hash::make('password123'),
            'role_id' => $staff->id
        ]);



        User::create([
            'name'     => 'Operational',
            'email'    => 'operational@logistics.com',
            'password' => Hash::make('password123'),
            'role_id' => $operational->id
        ]);

        $this->call([
            DistrictSeeder::class,
            SubDistrictSeeder::class,
            VehicleSeeder::class,
            CustomerSeeder::class,
            BankAccountSeeder::class
        ]);

        $customer = Customer::first();
        $subDistrict_one = SubDistrict::first();
        $subDistrict_two = SubDistrict::all()->last();
        $tripPrice = TripPrice::create([
            'base_price' => 50000,
            'customer_id' => $customer->id,
            'origin_sub_district_id' => $subDistrict_one->id,
            'dest_sub_district_id' => $subDistrict_two->id
        ]);

        $vehicle = Vehicle::first();
        $bankAccount = BankAccount::first();

        $transaction = Transaction::create([
            'transaction_capacity' => 21,
            'transaction_items' => "pipa rucika",
            'dest_address' => "jalan lampung 003",
            'customer_id' => $customer->id,
            'origin_sub_district_id' => $subDistrict_one->id,
            'dest_sub_district_id' => $subDistrict_two->id,
            'vehicle_id' => $vehicle->id,
            'bank_account_id' => $bankAccount->id
        ]);
    }
}





