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
        // Kolom mungkin sudah terbuat tanpa FK dari attempt sebelumnya
        if (!Schema::hasColumn('maintenance_schedules', 'location_id')) {
            Schema::table('maintenance_schedules', function (Blueprint $table) {
                $table->foreignId('location_id')
                      ->nullable()
                      ->after('asset_id')
                      ->constrained('locations')
                      ->nullOnDelete();
            });
        } else {
            // Kolom sudah ada, hanya tambahkan FK constraint
            Schema::table('maintenance_schedules', function (Blueprint $table) {
                $table->foreign('location_id')
                      ->references('id')->on('locations')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });
    }
};
