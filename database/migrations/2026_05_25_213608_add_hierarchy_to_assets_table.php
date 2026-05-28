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
            $table->string('transformer_block')->nullable()->after('location');
            $table->unsignedInteger('string_number')->nullable()->after('transformer_block');
            $table->unsignedInteger('module_slot')->nullable()->after('string_number');

            // Composite index for hierarchy lookups
            $table->index(['location_id', 'transformer_block', 'string_number', 'module_slot'], 'idx_asset_hierarchy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex('idx_asset_hierarchy');
            $table->dropColumn(['transformer_block', 'string_number', 'module_slot']);
        });
    }
};
