<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Driver;

class DriverSeeder extends Seeder
{
    public function run()
    {
        Driver::create([
            'name'     => 'driver-anton'
        ]);

        Driver::create([
            'name'     => 'driver-benny'
        ]);
    }
}
