<?php

namespace Tests\Feature;

use App\Models\SparePart;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testAdjustStockAdd()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $sparePart = SparePart::factory()->create(['qty_actual' => 5]);

        $this->actingAs($user)
            ->post(route('spare-parts.adjust-stock', $sparePart), [
                'type' => 'add',
                'quantity' => 10,
            ])
            ->assertRedirect();

        $sparePart->refresh();
        $this->assertEquals(15, $sparePart->qty_actual);
        $this->assertDatabaseHas('stock_movements', [
            'spare_part_id' => $sparePart->id,
            'mutation_type' => 'add',
            'qty' => 10,
        ]);
    }

    /** @test */
    public function testAdjustStockDeductFails()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $sparePart = SparePart::factory()->create(['qty_actual' => 5]);

        $response = $this->actingAs($user)
            ->post(route('spare-parts.adjust-stock', $sparePart), [
                'type' => 'reduce',
                'quantity' => 10,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $sparePart->refresh();
        $this->assertEquals(5, $sparePart->qty_actual);
    }
}
