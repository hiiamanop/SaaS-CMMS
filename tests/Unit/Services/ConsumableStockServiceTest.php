<?php

namespace Tests\Unit\Services;

use App\Models\Consumable;
use App\Services\StockService;
use App\Exceptions\OutOfStockException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsumableStockServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testDeductConsumableReducesStock()
    {
        $c = Consumable::create([
            'item_code' => 'C-1', 'name' => 'Oli', 'unit' => 'liter',
            'qty_actual' => 10, 'qty_minimum' => 0,
        ]);

        StockService::deductConsumable($c, 3, 1);

        $this->assertEquals(7, $c->fresh()->qty_actual);
    }

    /** @test */
    public function testDeductConsumableThrowsWhenInsufficient()
    {
        $c = Consumable::create([
            'item_code' => 'C-2', 'name' => 'Majun', 'unit' => 'pcs',
            'qty_actual' => 2, 'qty_minimum' => 0,
        ]);

        $this->expectException(OutOfStockException::class);
        StockService::deductConsumable($c, 5, 1);
    }
}
