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
        Schema::create('field_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->string('field_name');
            $table->string('label');
            $table->boolean('is_disabled')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
            $table->unique(['module', 'field_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_configurations');
    }
};
