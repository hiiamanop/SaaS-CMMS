<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop check constraint for Postgres if it exists (fix for SQLSTATE[23514])
        if (config('database.default') === 'pgsql') {
            \DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        }

        // Insert Role if not exists
        \DB::table('roles')->updateOrInsert(
            ['name' => 'developer'],
            [
                'label'       => 'Developer / Super Admin',
                'description' => 'Role khusus dengan akses penuh ke konfigurasi sistem.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );

        // Insert User if not exists
        \DB::table('users')->updateOrInsert(
            ['email' => 'developer@cmms.com'],
            [
                'name'       => 'Developer Account',
                'password'   => \Hash::make('dev12345'),
                'role'       => 'developer',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        // Keep the role but we could delete the user
        \DB::table('users')->where('email', 'developer@cmms.com')->delete();
    }
};
