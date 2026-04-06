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
        Schema::create('checksheet_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checksheet_type_id')->constrained()->cascadeOnDelete();
            $table->string('plts_location');
            $table->string('equipment_location')->nullable();
            $table->string('period_label');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('week_number')->nullable();
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedTinyInteger('semester')->nullable();
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('signed_by_teknisi')->nullable();
            $table->date('signed_date_teknisi')->nullable();
            $table->string('signed_by_spv')->nullable();
            $table->date('signed_date_spv')->nullable();
            $table->string('signed_by_pm')->nullable();
            $table->date('signed_date_pm')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checksheet_sessions');
    }
};
