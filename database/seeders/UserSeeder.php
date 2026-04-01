<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['name' => 'Admin User', 'email' => 'admin@cmms.com', 'password' => Hash::make('password'), 'role' => 'admin', 'phone' => '081200000001']);
        User::create(['name' => 'John Supervisor', 'email' => 'supervisor@cmms.com', 'password' => Hash::make('password'), 'role' => 'supervisor', 'phone' => '081200000002']);
        User::create(['name' => 'Alice Technician', 'email' => 'tech1@cmms.com', 'password' => Hash::make('password'), 'role' => 'technician', 'phone' => '081200000003']);
        User::create(['name' => 'Bob Technician', 'email' => 'tech2@cmms.com', 'password' => Hash::make('password'), 'role' => 'technician', 'phone' => '081200000004']);
    }
}
