<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('active','inactive','replaced','retired') DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('active','inactive','under_maintenance','retired') DEFAULT 'active'");
    }
};
