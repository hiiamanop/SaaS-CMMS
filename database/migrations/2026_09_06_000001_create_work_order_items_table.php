<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 30);
            $table->unsignedBigInteger('item_id');
            $table->unsignedInteger('qty_used')->default(1);
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('used_at')->useCurrent();
            $table->timestamps();

            $table->index(['item_type', 'item_id', 'used_at']);
            $table->index(['work_order_id', 'item_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_items');
    }
};
