<?php

namespace Tests\Unit\Services;

use App\Models\SparePart;
use App\Models\StockMovement;
use App\Services\StockService;
use App\Exceptions\OutOfStockException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testDeductSucceedsWhenSufficientStock()
    {
        $sparePart = SparePart::factory()->create(['qty_actual' => 10]);

        StockService::deduct($sparePart, 3, 'maintenance_record', 1);

        $sparePart->refresh();
        $this->assertEquals(7, $sparePart->qty_actual);
        $this->assertDatabaseHas('stock_movements', [
            'spare_part_id' => $sparePart->id,
            'mutation_type' => 'deduct',
            'qty' => 3,
            'reason' => 'maintenance_record',
            'created_by_user_id' => 1,
        ]);
    }

    /** @test */
    public function testDeductThrowsOutOfStockException()
    {
        $sparePart = SparePart::factory()->create(['qty_actual' => 2]);

        $this->expectException(OutOfStockException::class);
        StockService::deduct($sparePart, 5, 'maintenance_record', 1);

        $sparePart->refresh();
        $this->assertEquals(2, $sparePart->qty_actual);
    }

    /** @test */
    public function testAddAlwaysSucceeds()
    {
        $sparePart = SparePart::factory()->create(['qty_actual' => 5]);

        StockService::add($sparePart, 10, 'stock_adjustment', 1);

        $sparePart->refresh();
        $this->assertEquals(15, $sparePart->qty_actual);
        $this->assertDatabaseHas('stock_movements', [
            'spare_part_id' => $sparePart->id,
            'mutation_type' => 'add',
            'qty' => 10,
        ]);
    }

    /** @test */
    public function testStockMovementRecorded()
    {
        $sparePart = SparePart::factory()->create(['qty_actual' => 20]);

        StockService::deduct($sparePart, 5, 'maintenance_record', 2);

        $movement = StockMovement::where('spare_part_id', $sparePart->id)->first();
        $this->assertNotNull($movement);
        $this->assertEquals('deduct', $movement->mutation_type);
        $this->assertEquals(5, $movement->qty);
        $this->assertEquals('maintenance_record', $movement->reason);
        $this->assertEquals(2, $movement->created_by_user_id);
    }

    /** @test */
    public function testConcurrentDeductIsAtomic()
    {
        $sparePart = SparePart::factory()->create(['qty_actual' => 5]);

        StockService::deduct($sparePart, 3, 'maintenance_record', 1);
        $sparePart->refresh();
        $this->assertEquals(2, $sparePart->qty_actual);

        $this->expectException(OutOfStockException::class);
        StockService::deduct($sparePart, 3, 'maintenance_record', 1);

        $sparePart->refresh();
        $this->assertEquals(2, $sparePart->qty_actual);
    }
}
