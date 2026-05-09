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
        Schema::table('work_orders', function (Blueprint $table) {
            $table->boolean('override_on_time')->default(false)->after('status');
        });

        Schema::table('checksheet_sessions', function (Blueprint $table) {
            $table->boolean('override_on_time')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('override_on_time');
        });

        Schema::table('checksheet_sessions', function (Blueprint $table) {
            $table->dropColumn('override_on_time');
        });
    }
};
