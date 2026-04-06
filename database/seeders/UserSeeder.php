<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create(['name' => 'Admin User', 'email' => 'admin@cmms.com', 'password' => Hash::make('password'), 'role' => 'admin', 'phone' => '081200000001', 'is_active' => true]);
        User::create(['name' => 'Budi Supervisor', 'email' => 'spv@cmms.com', 'password' => Hash::make('password'), 'role' => 'supervisor', 'phone' => '081200000002', 'is_active' => true]);
        User::create(['name' => 'Andi Teknisi', 'email' => 'teknisi1@cmms.com', 'password' => Hash::make('password'), 'role' => 'technician', 'phone' => '081200000003', 'is_active' => true]);
        User::create(['name' => 'Rudi Teknisi', 'email' => 'teknisi2@cmms.com', 'password' => Hash::make('password'), 'role' => 'technician', 'phone' => '081200000004', 'is_active' => true]);
        User::create(['name' => 'Sari PM', 'email' => 'pm@cmms.com', 'password' => Hash::make('password'), 'role' => 'pm', 'phone' => '081200000005', 'is_active' => true]);
    }
}
