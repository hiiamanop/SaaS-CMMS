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
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->string('type', 32)->change(); // Expand from enum to string for PLTS types
            $table->string('category')->nullable()->after('asset_id');
            $table->string('equipment_name')->nullable()->after('category');
            $table->text('item_pekerjaan')->nullable()->after('equipment_name');
            $table->json('planned_weeks')->nullable()->after('item_pekerjaan');   // [{month,week}]
            $table->boolean('shutdown_required')->default(false)->after('planned_weeks');
            $table->unsignedSmallInteger('shutdown_duration_hours')->nullable()->after('shutdown_required');
            $table->json('checklist_template')->nullable()->after('shutdown_duration_hours');
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->enum('type', ['preventive', 'corrective'])->default('preventive')->change();
            $table->dropColumn([
                'category','equipment_name','item_pekerjaan',
                'planned_weeks','shutdown_required','shutdown_duration_hours','checklist_template',
            ]);
        });
    }
};
