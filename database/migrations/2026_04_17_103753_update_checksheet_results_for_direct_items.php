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
        Schema::table('checksheet_results', function (Blueprint $table) {
            $table->string('item_name')->nullable()->after('session_id');
            $table->foreignId('template_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checksheet_results', function (Blueprint $table) {
            $table->dropColumn('item_name');
            $table->foreignId('template_id')->nullable(false)->change();
        });
    }
};
