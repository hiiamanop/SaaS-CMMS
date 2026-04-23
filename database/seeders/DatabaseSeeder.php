<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LocationSeeder::class,
            UserSeeder::class,
            AssetSeeder::class,
            SparePartSeeder::class,
            // MaintenanceScheduleSeeder::class,
            // ChecksheetTemplateSeeder::class,
            // WorkOrderSeeder::class,
            // MaintenanceRecordSeeder::class,
            // NotificationSeeder::class,
        ]);
    }
}
