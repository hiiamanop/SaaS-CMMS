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
        Schema::table('assets', function (Blueprint $table) {
            $table->string('asset_code')->nullable()->change();
        });

        Schema::table('tools', function (Blueprint $table) {
            $table->string('tool_code')->nullable()->change();
        });

        Schema::table('spare_parts', function (Blueprint $table) {
            $table->string('part_code')->nullable()->change();
        });

        Schema::table('consumables', function (Blueprint $table) {
            $table->string('item_code')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('asset_code')->nullable(false)->change();
        });

        Schema::table('tools', function (Blueprint $table) {
            $table->string('tool_code')->nullable(false)->change();
        });

        Schema::table('spare_parts', function (Blueprint $table) {
            $table->string('part_code')->nullable(false)->change();
        });

        Schema::table('consumables', function (Blueprint $table) {
            $table->string('item_code')->nullable(false)->change();
        });
    }
};
