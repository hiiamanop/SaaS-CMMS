<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert existing plain-text values to JSON arrays
        DB::table('maintenance_schedules')
            ->whereNotNull('item_pekerjaan')
            ->get()
            ->each(function ($row) {
                $val = $row->item_pekerjaan;
                $decoded = json_decode($val, true);
                if (!is_array($decoded)) {
                    // Split by comma to preserve multiple items if any
                    $items = array_values(array_filter(array_map('trim', explode(',', $val))));
                    DB::table('maintenance_schedules')
                        ->where('id', $row->id)
                        ->update(['item_pekerjaan' => json_encode($items ?: [$val])]);
                }
            });
    }

    public function down(): void
    {
        DB::table('maintenance_schedules')
            ->whereNotNull('item_pekerjaan')
            ->get()
            ->each(function ($row) {
                $decoded = json_decode($row->item_pekerjaan, true);
                if (is_array($decoded)) {
                    DB::table('maintenance_schedules')
                        ->where('id', $row->id)
                        ->update(['item_pekerjaan' => implode(', ', $decoded)]);
                }
            });
    }
};
