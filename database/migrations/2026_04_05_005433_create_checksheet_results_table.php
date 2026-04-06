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
        Schema::create('checksheet_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('checksheet_sessions')->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('checksheet_templates')->cascadeOnDelete();
            $table->enum('result', ['P', 'X'])->nullable();
            $table->text('notes')->nullable();
            $table->json('photos')->nullable();
            $table->timestamps();
            $table->unique(['session_id', 'template_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checksheet_results');
    }
};
