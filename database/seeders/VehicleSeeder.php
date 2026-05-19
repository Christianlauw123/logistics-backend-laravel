<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;

class VehicleSeeder extends Seeder
{
    public function run()
    {
        Vehicle::create([
            'name' => 'mobil1',
            'plate_number'     => 'B1134',
            'type'    => 'elf',
            'capacity' => 8,
        ]);

        Vehicle::create([
            'name' => 'mobil2',
            'plate_number'     => 'AG1134',
            'type'    => 'tronton',
            'capacity' => 35,
        ]);
    }
}
