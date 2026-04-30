<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_report_id')->constrained()->onDelete('cascade');
            $table->foreignId('sector_id')->constrained('production_sectors')->onDelete('cascade');
            $table->decimal('kwh_trafo', 14, 2)->nullable();
            $table->decimal('kwh_meter', 14, 2)->nullable();
            $table->decimal('sun_hour', 6, 2)->nullable();
            $table->decimal('capacity_factor', 8, 4)->nullable();
            $table->timestamps();

            $table->unique(['production_report_id', 'sector_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_entries');
    }
};
