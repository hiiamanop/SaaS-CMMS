<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AssetSeeder::class,
            SparePartSeeder::class,
            MaintenanceScheduleSeeder::class,
            WorkOrderSeeder::class,
            MaintenanceRecordSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
