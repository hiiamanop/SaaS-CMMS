<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('findings', function (Blueprint $table) {
            $table->dropColumn(['severity', 'pic']);
            $table->string('item_name')->nullable()->after('checksheet_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('findings', function (Blueprint $table) {
            $table->dropColumn('item_name');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('pic')->nullable();
        });
    }
};
