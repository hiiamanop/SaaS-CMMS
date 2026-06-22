<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pv_maps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('transformer_block')->index(); // T01, T02, etc
            $table->enum('map_status', ['draft', 'published'])->default('draft');
            $table->json('map_data')->nullable(); // [{id, module_id, visual_row, visual_col}, ...]
            $table->timestamps();

            $table->unique(['location_id', 'transformer_block']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pv_maps');
    }
};
