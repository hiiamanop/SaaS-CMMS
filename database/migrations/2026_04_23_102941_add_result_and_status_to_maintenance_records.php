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
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->enum('status_after', ['solved', 'pending', 'failure'])->default('solved')->after('notes');
        });

        Schema::table('work_order_checklist_items', function (Blueprint $table) {
            $table->string('result')->nullable()->after('is_checked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_records', function (Blueprint $table) {
            $table->dropColumn('status_after');
        });

        Schema::table('work_order_checklist_items', function (Blueprint $table) {
            $table->dropColumn('result');
        });
    }
};
