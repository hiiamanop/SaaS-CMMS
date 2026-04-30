<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            // Jika kolom belum ada, tambahkan
            if (!Schema::hasColumn('work_orders', 'start_date')) {
                $table->date('start_date')->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('work_orders', 'assigned_to_external')) {
                $table->string('assigned_to_external')->nullable()->after('assigned_to');
            }

            // Gunakan change() agar Laravel yang mengurus perbedaan sintaks antar database
            $table->string('type')->default('corrective')->change();
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'assigned_to_external']);
            $table->string('type')->default('corrective')->change();
        });
    }
};
