<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumables', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('name');
            $table->string('category')->nullable(); // e.g. Lubrication, Cleaning, Safety
            $table->string('unit')->default('pcs');
            $table->integer('qty_actual')->default(0);
            $table->integer('qty_minimum')->default(0);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->string('supplier')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumables');
    }
};
