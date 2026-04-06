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
        Schema::create('checksheet_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Mingguan|Bulanan|Semesteran|Tahunan
            $table->string('frequency'); // weekly|monthly|semester|yearly
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checksheet_types');
    }
};
