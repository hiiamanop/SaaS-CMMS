<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            ['name' => 'PLTS Atap Gedung Utama',     'capacity_mwp' => 100.00, 'address' => 'Gedung Utama, Lt. Atap'],
            ['name' => 'PLTS Atap Gedung Produksi',   'capacity_mwp' => 250.00, 'address' => 'Gedung Produksi, Lt. Atap'],
            ['name' => 'PLTS Ground-Mounted Area A',  'capacity_mwp' => 500.00, 'address' => 'Area A, Lapangan Barat'],
            ['name' => 'PLTS Ground-Mounted Area B',  'capacity_mwp' => 500.00, 'address' => 'Area B, Lapangan Timur'],
            ['name' => 'PLTS Carport Parkir Selatan', 'capacity_mwp' =>  80.00, 'address' => 'Area Parkir Selatan'],
        ];

        foreach ($locations as $loc) {
            \App\Models\Location::create(array_merge($loc, ['is_active' => true]));
        }
    }
}
