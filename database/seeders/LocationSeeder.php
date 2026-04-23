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
            ['name' => 'PLTS Atap Gedung Utama',     'code' => 'PLTS-01', 'capacity_kwp' => 100.00, 'address' => 'Gedung Utama, Lt. Atap'],
            ['name' => 'PLTS Atap Gedung Produksi',   'code' => 'PLTS-02', 'capacity_kwp' => 250.00, 'address' => 'Gedung Produksi, Lt. Atap'],
            ['name' => 'PLTS Ground-Mounted Area A',  'code' => 'PLTS-03', 'capacity_kwp' => 500.00, 'address' => 'Area A, Lapangan Barat'],
            ['name' => 'PLTS Ground-Mounted Area B',  'code' => 'PLTS-04', 'capacity_kwp' => 500.00, 'address' => 'Area B, Lapangan Timur'],
            ['name' => 'PLTS Carport Parkir Selatan', 'code' => 'PLTS-05', 'capacity_kwp' =>  80.00, 'address' => 'Area Parkir Selatan'],
        ];

        foreach ($locations as $loc) {
            \App\Models\Location::create(array_merge($loc, ['is_active' => true]));
        }
    }
}
