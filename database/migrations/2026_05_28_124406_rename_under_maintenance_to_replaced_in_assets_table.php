<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE assets DROP CONSTRAINT IF EXISTS assets_status_check");
            DB::statement("ALTER TABLE assets ADD CONSTRAINT assets_status_check CHECK (status IN ('active','inactive','replaced','retired'))");
        } elseif (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite does not support MODIFY COLUMN
        } else {
            DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('active','inactive','replaced','retired') DEFAULT 'active'");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE assets DROP CONSTRAINT IF EXISTS assets_status_check");
            DB::statement("ALTER TABLE assets ADD CONSTRAINT assets_status_check CHECK (status IN ('active','inactive','under_maintenance','retired'))");
        } elseif (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite does not support MODIFY COLUMN
        } else {
            DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('active','inactive','under_maintenance','retired') DEFAULT 'active'");
        }
    }
};
