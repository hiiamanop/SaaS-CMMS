<?php

namespace App\Services;

use App\Exceptions\OutOfStockException;
use App\Models\Consumable;
use App\Models\SparePart;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Deduct stock (pemakaian / adjustment).
     * @throws OutOfStockException if insufficient stock
     */
    public static function deduct(SparePart $sparePart, int $qty, string $reason, int $userId): void
    {
        try {
            DB::transaction(function () use ($sparePart, $qty, $reason, $userId) {
                $sparePart = SparePart::lockForUpdate()->findOrFail($sparePart->id);

                if ($sparePart->qty_actual < $qty) {
                    throw new OutOfStockException($sparePart, $sparePart->qty_actual, $qty);
                }

                $sparePart->decrement('qty_actual', $qty);

                StockMovement::create([
                    'spare_part_id' => $sparePart->id,
                    'mutation_type' => 'deduct',
                    'qty' => $qty,
                    'reason' => $reason,
                    'created_by_user_id' => $userId,
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'lock wait timeout')) {
                \Log::warning("Stock lock timeout for spare_part_id={$sparePart->id}");
                throw new \Exception('Database temporarily locked. Please try again.', 503);
            }
            throw $e;
        }
    }

    /**
     * Add stock (penerimaan baru).
     * Always succeeds, no validation.
     */
    public static function add(SparePart $sparePart, int $qty, string $reason, int $userId): void
    {
        DB::transaction(function () use ($sparePart, $qty, $reason, $userId) {
            $sparePart = SparePart::lockForUpdate()->findOrFail($sparePart->id);

            $sparePart->increment('qty_actual', $qty);

            StockMovement::create([
                'spare_part_id' => $sparePart->id,
                'mutation_type' => 'add',
                'qty' => $qty,
                'reason' => $reason,
                'created_by_user_id' => $userId,
            ]);
        });
    }

    /**
     * Deduct consumable stock (pemakaian). No ledger table — line items are the audit trail.
     * @throws OutOfStockException if insufficient stock
     */
    public static function deductConsumable(Consumable $consumable, int $qty, int $userId): void
    {
        DB::transaction(function () use ($consumable, $qty) {
            $consumable = Consumable::lockForUpdate()->findOrFail($consumable->id);

            if ($consumable->qty_actual < $qty) {
                throw new OutOfStockException($consumable, $consumable->qty_actual, $qty);
            }

            $consumable->decrement('qty_actual', $qty);
        });
    }
}
