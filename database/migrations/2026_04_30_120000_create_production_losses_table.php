<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_losses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->foreignId('sector_id')->nullable()->constrained('production_sectors')->onDelete('set null');
            $table->foreignId('work_order_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            // Event details
            $table->datetime('started_at');
            $table->datetime('ended_at')->nullable();
            $table->unsignedInteger('duration_minutes')->default(0);

            // Location detail (trafo, inverter, string)
            $table->string('trafo')->nullable();
            $table->string('inverter')->nullable();
            $table->string('string_info')->nullable();
            $table->unsignedInteger('affected_strings')->nullable();

            // Category & description
            $table->enum('category', [
                'planned_maintenance',
                'corrective_maintenance',
                'equipment_fault',
                'grid_fault',
                'natural',
                'other',
            ])->default('other');
            $table->text('description')->nullable();

            // LOP values
            $table->decimal('affected_capacity_kw', 10, 2)->nullable();
            $table->decimal('lop_kwh', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_losses');
    }
};
