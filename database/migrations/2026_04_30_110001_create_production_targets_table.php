<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->foreignId('sector_id')->nullable()->constrained('production_sectors')->onDelete('cascade');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('target_kwh_daily', 12, 2)->nullable();
            $table->decimal('target_pr', 5, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['location_id', 'sector_id', 'year', 'month'], 'prod_target_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_targets');
    }
};
