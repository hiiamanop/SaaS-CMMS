<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('report_date');
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'report_date']); // One report per user per day
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
