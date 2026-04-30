<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->date('report_date');
            // Weather summary (hours per condition)
            $table->decimal('cerah_hours', 4, 2)->default(0);
            $table->decimal('berawan_hours', 4, 2)->default(0);
            $table->decimal('mendung_hours', 4, 2)->default(0);
            $table->decimal('hujan_hours', 4, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['location_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_reports');
    }
};
