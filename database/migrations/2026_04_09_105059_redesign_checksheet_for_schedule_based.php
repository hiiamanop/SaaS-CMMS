<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bersihkan data lama (child dulu baru parent)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('checksheet_results')->truncate();
        DB::table('checksheet_abnormals')->truncate();
        DB::table('checksheet_sessions')->truncate();
        DB::table('checksheet_templates')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // 1. Tambah technician_id ke maintenance_schedules
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->foreignId('technician_id')
                  ->nullable()
                  ->after('asset_id')
                  ->constrained('users')
                  ->nullOnDelete();
        });

        // 2. Ubah checksheet_templates: ganti checksheet_type_id → maintenance_schedule_id
        Schema::table('checksheet_templates', function (Blueprint $table) {
            $table->dropForeign(['checksheet_type_id']);
            $table->dropColumn('checksheet_type_id');
        });
        Schema::table('checksheet_templates', function (Blueprint $table) {
            $table->foreignId('maintenance_schedule_id')
                  ->after('id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('metode_inspeksi')->nullable()->change();
            $table->string('standar_ketentuan')->nullable()->change();
        });

        // 3. Ubah checksheet_sessions: ganti checksheet_type_id → maintenance_schedule_id
        Schema::table('checksheet_sessions', function (Blueprint $table) {
            $table->dropForeign(['checksheet_type_id']);
            $table->dropColumn('checksheet_type_id');
        });
        Schema::table('checksheet_sessions', function (Blueprint $table) {
            $table->foreignId('maintenance_schedule_id')
                  ->after('id')
                  ->constrained()
                  ->cascadeOnDelete();
        });

        // 4. Drop tabel checksheet_types (sudah tidak diperlukan)
        Schema::dropIfExists('checksheet_types');
    }

    public function down(): void
    {
        Schema::create('checksheet_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('frequency');
            $table->timestamps();
        });

        Schema::table('checksheet_sessions', function (Blueprint $table) {
            $table->dropForeign(['maintenance_schedule_id']);
            $table->dropColumn('maintenance_schedule_id');
            $table->foreignId('checksheet_type_id')->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('checksheet_templates', function (Blueprint $table) {
            $table->dropForeign(['maintenance_schedule_id']);
            $table->dropColumn('maintenance_schedule_id');
            $table->foreignId('checksheet_type_id')->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->dropForeign(['technician_id']);
            $table->dropColumn('technician_id');
        });
    }
};
