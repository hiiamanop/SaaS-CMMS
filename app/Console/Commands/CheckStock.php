<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\SparePart;
use App\Models\User;
use Illuminate\Console\Command;

class CheckStock extends Command
{
    protected $signature = 'cmms:check-stock';
    protected $description = 'Check spare parts below minimum stock and notify';

    public function handle(): void
    {
        $lowStock = SparePart::whereColumn('qty_actual', '<=', 'qty_minimum')->get();
        $adminsAndSpvs = User::whereIn('role', ['admin', 'supervisor'])->get();

        foreach ($lowStock as $part) {
            foreach ($adminsAndSpvs as $user) {
                Notification::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'type' => 'low_stock',
                        'data->spare_part_id' => $part->id,
                        'is_read' => false,
                    ],
                    [
                        'title' => 'Stok Spare Part Rendah',
                        'message' => "Stok {$part->name} ({$part->code}) tersisa {$part->qty_actual} {$part->unit} (minimum: {$part->qty_minimum}).",
                        'data' => ['spare_part_id' => $part->id],
                        'is_read' => false,
                    ]
                );
            }
        }

        $this->info("Checked {$lowStock->count()} low-stock parts.");
    }
}
