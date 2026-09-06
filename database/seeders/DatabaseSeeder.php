<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            UserSeeder::class,
            FieldConfigurationSeeder::class,
            LestariPertiwiSeeder::class,
            StockOpnameSeeder::class,
            MaintenanceScheduleSeeder::class,
            ChecksheetTemplateSeeder::class,
            WorkOrderSeeder::class,
            MaintenanceRecordSeeder::class,
        ]);
    }
}
