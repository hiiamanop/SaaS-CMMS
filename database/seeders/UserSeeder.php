<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $loc1 = \App\Models\Location::where('code', 'PLTS-01')->first()?->id;
        $loc2 = \App\Models\Location::where('code', 'PLTS-02')->first()?->id;
        $loc3 = \App\Models\Location::where('code', 'PLTS-03')->first()?->id;

        User::create(['name' => 'Admin User',    'email' => 'admin@cmms.com',    'password' => Hash::make('password'), 'role' => 'admin',      'phone' => '081200000001', 'is_active' => true, 'location_id' => null])->assignRole('admin');
        User::create(['name' => 'Budi Supervisor','email' => 'spv@cmms.com',     'password' => Hash::make('password'), 'role' => 'supervisor', 'phone' => '081200000002', 'is_active' => true, 'location_id' => $loc1])->assignRole('supervisor');
        User::create(['name' => 'Andi Teknisi',  'email' => 'teknisi1@cmms.com', 'password' => Hash::make('password'), 'role' => 'technician', 'phone' => '081200000003', 'is_active' => true, 'location_id' => $loc1])->assignRole('technician');
        User::create(['name' => 'Rudi Teknisi',  'email' => 'teknisi2@cmms.com', 'password' => Hash::make('password'), 'role' => 'technician', 'phone' => '081200000004', 'is_active' => true, 'location_id' => $loc2])->assignRole('technician');
        User::create(['name' => 'Sari PM',       'email' => 'pm@cmms.com',       'password' => Hash::make('password'), 'role' => 'pm',         'phone' => '081200000005', 'is_active' => true, 'location_id' => $loc3])->assignRole('pm');

        // Hidden Super Admin
        User::create([
            'name' => 'System Support',
            'email' => 'wakwaw@gmail.com',
            'password' => Hash::make('ayamgoyengenak'),
            'role' => 'super-admin',
            'is_active' => true
        ])->assignRole('super-admin');

        // Developer
        User::create([
            'name' => 'Developer',
            'email' => 'developer@cmms.com',
            'password' => Hash::make('password'),
            'role' => 'developer',
            'is_active' => true
        ])->assignRole('developer');
    }
}
