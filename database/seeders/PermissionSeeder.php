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

        // Create/Update roles
        $admin      = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Admin', 'guard_name' => 'web']);
        $developer  = Role::firstOrCreate(['name' => 'developer'], ['label' => 'Developer', 'guard_name' => 'web']);
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin'], ['label' => 'Super Admin', 'guard_name' => 'web']);
        $supervisor = Role::firstOrCreate(['name' => 'supervisor'], ['label' => 'Supervisor', 'guard_name' => 'web']);
        $technician = Role::firstOrCreate(['name' => 'technician'], ['label' => 'Technician', 'guard_name' => 'web']);
        $pm         = Role::firstOrCreate(['name' => 'pm'], ['label' => 'Project Manager', 'guard_name' => 'web']);

        // Assign permissions to roles
        if ($admin) {
            $admin->syncPermissions(Permission::all());
        }

        if ($developer) {
            $developer->syncPermissions(Permission::all());
        }

        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
        }

        if ($supervisor) {
            $supervisor->syncPermissions([
                'view-assets', 'view-consumables', 'view-tools', 'view-spare-parts',
                'view-work-orders', 'create-work-orders', 'edit-work-orders', 'assign-work-orders'
            ]);
        }

        if ($technician) {
            $technician->syncPermissions([
                'view-assets', 'view-consumables', 'view-tools', 'view-spare-parts',
                'view-work-orders'
            ]);
        }
    }
}
