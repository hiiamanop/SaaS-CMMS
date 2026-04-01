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
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->string('record_number')->unique();
            $table->foreignId('work_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('asset_id')->constrained();
            $table->foreignId('technician_id')->constrained('users');
            $table->enum('type', ['preventive', 'corrective'])->default('corrective');
            $table->date('maintenance_date');
            $table->text('findings')->nullable();
            $table->text('actions_taken')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->integer('downtime_minutes')->default(0);
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
        Schema::dropIfExists('maintenance_records');
    }
};
