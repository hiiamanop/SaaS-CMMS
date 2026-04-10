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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();  // slug used in users.role
            $table->string('label');            // display name
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed existing roles
        DB::table('roles')->insert([
            ['name' => 'admin',      'label' => 'Admin',      'description' => 'Full system access', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'supervisor', 'label' => 'Supervisor', 'description' => 'Manage work orders and team', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'technician', 'label' => 'Technician', 'description' => 'Execute maintenance tasks', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'pm',         'label' => 'Project Manager', 'description' => 'Project oversight', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
