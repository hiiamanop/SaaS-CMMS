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
        Schema::table('findings', function (Blueprint $table) {
            $table->dateTime('finding_time')->nullable()->after('found_date');
            $table->dateTime('close_time')->nullable()->after('resolved_date');
        });
    }

    public function down(): void
    {
        Schema::table('findings', function (Blueprint $table) {
            $table->dropColumn(['finding_time', 'close_time']);
        });
    }
};
