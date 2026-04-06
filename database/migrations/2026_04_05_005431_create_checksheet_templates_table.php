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
        Schema::create('checksheet_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checksheet_type_id')->constrained()->cascadeOnDelete();
            $table->string('lokasi_inspeksi');
            $table->string('item_inspeksi');
            $table->string('metode_inspeksi');
            $table->string('standar_ketentuan');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checksheet_templates');
    }
};
