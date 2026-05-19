<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        Customer::create([
            'name'     => 'doddy',
            'phone'    => '1234',
            'address' => 'malam rindu',
        ]);

        Customer::create([
            'name'     => 'benny',
            'phone'    => '1234',
            'address' => 'siang panas',
        ]);
    }
}
