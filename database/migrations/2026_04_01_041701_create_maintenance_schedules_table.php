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
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['preventive', 'corrective'])->default('preventive');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'annually', 'custom'])->default('monthly');
            $table->integer('frequency_days')->nullable();
            $table->date('start_date');
            $table->date('next_due_date');
            $table->date('last_done_date')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
