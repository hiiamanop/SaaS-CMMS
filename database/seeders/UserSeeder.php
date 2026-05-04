<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $locs = \App\Models\Location::all();
        $loc1 = $locs->skip(0)->first()?->id;
        $loc2 = $locs->skip(1)->first()?->id;
        $loc3 = $locs->skip(2)->first()?->id;

        User::updateOrCreate(['email' => 'admin@cmms.com'], ['name' => 'Admin User',    'password' => Hash::make('password'), 'role' => 'admin',      'phone' => '081200000001', 'is_active' => true, 'location_id' => null])->assignRole('admin');
        User::updateOrCreate(['email' => 'spv@cmms.com'], ['name' => 'Budi Supervisor',     'password' => Hash::make('password'), 'role' => 'supervisor', 'phone' => '081200000002', 'is_active' => true, 'location_id' => $loc1])->assignRole('supervisor');
        User::updateOrCreate(['email' => 'teknisi1@cmms.com'], ['name' => 'Andi Teknisi',  'password' => Hash::make('password'), 'role' => 'technician', 'phone' => '081200000003', 'is_active' => true, 'location_id' => $loc1])->assignRole('technician');
        User::updateOrCreate(['email' => 'teknisi2@cmms.com'], ['name' => 'Rudi Teknisi',  'password' => Hash::make('password'), 'role' => 'technician', 'phone' => '081200000004', 'is_active' => true, 'location_id' => $loc2])->assignRole('technician');
        User::updateOrCreate(['email' => 'pm@cmms.com'], ['name' => 'Sari PM',       'password' => Hash::make('password'), 'role' => 'pm',         'phone' => '081200000005', 'is_active' => true, 'location_id' => $loc3])->assignRole('pm');

        // Hidden Super Admin
        User::updateOrCreate(['email' => 'wakwaw@gmail.com'], [
            'name' => 'System Support',
            'password' => Hash::make('ayamgoyengenak'),
            'role' => 'super-admin',
            'is_active' => true
        ])->assignRole('super-admin');

        // Developer
        User::updateOrCreate(['email' => 'developer@cmms.com'], [
            'name' => 'Developer',
            'password' => Hash::make('password'),
            'role' => 'developer',
            'is_active' => true
        ])->assignRole('developer');
    }
}
