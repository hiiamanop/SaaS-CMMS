<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('due_date');
            $table->string('assigned_to_external')->nullable()->after('assigned_to');
            // Change type enum to support all required types - handled via string column modification
        });

        // Update type enum to support all types
        DB::statement("ALTER TABLE work_orders MODIFY COLUMN type ENUM('preventive_mingguan','preventive_bulanan','preventive_semesteran','preventive_tahunan','corrective','emergency') DEFAULT 'corrective'");
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'assigned_to_external']);
        });
        DB::statement("ALTER TABLE work_orders MODIFY COLUMN type ENUM('preventive','corrective') DEFAULT 'corrective'");
    }
};
