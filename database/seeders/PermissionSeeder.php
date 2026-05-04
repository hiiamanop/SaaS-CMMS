<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Assets
            'view-assets', 'create-assets', 'edit-assets', 'delete-assets',
            // Consumables
            'view-consumables', 'create-consumables', 'edit-consumables', 'delete-consumables',
            // Tools
            'view-tools', 'create-tools', 'edit-tools', 'delete-tools',
            // Spare Parts
            'view-spare-parts', 'create-spare-parts', 'edit-spare-parts', 'delete-spare-parts',
            // Maintenance / Work Orders
            'view-work-orders', 'create-work-orders', 'edit-work-orders', 'delete-work-orders', 'assign-work-orders',
            // Settings
            'manage-settings', 'manage-users', 'manage-roles', 'manage-locations', 'manage-fields'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign permissions to roles
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->syncPermissions(Permission::all());
        }

        $developer = Role::where('name', 'developer')->first();
        if ($developer) {
            $developer->syncPermissions(Permission::all());
        }

        $supervisor = Role::where('name', 'supervisor')->first();
        if ($supervisor) {
            $supervisor->syncPermissions([
                'view-assets', 'view-consumables', 'view-tools', 'view-spare-parts',
                'view-work-orders', 'create-work-orders', 'edit-work-orders', 'assign-work-orders'
            ]);
        }

        $technician = Role::where('name', 'technician')->first();
        if ($technician) {
            $technician->syncPermissions([
                'view-assets', 'view-consumables', 'view-tools', 'view-spare-parts',
                'view-work-orders'
            ]);
        }
    }
}
