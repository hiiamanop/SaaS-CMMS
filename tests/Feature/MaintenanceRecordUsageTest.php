<?php

namespace Tests\Feature;

use App\Models\Consumable;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceRecordUsageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testStoringRecordDeductsConsumableAndLogsTool()
    {
        $asset = \App\Models\Asset::create([
            'asset_code' => 'A-001',
            'name' => 'Test Asset',
            'category' => 'machinery',
            'location' => 'Test Location',
        ]);
        $tech = User::factory()->create(['role' => 'technician']);
        $consumable = Consumable::create([
            'item_code' => 'C-10', 'name' => 'Oli', 'unit' => 'liter',
            'qty_actual' => 10, 'qty_minimum' => 0,
        ]);
        $tool = Tool::create([
            'tool_code' => 'T-10', 'name' => 'Tang', 'condition' => 'good',
            'qty_total' => 5, 'qty_available' => 5,
        ]);

        $response = $this->actingAs($tech)->post(route('maintenance-records.store'), [
            'asset_id'         => $asset->id,
            'technician_id'    => $tech->id,
            'maintenance_date' => '2026-07-10',
            'duration_minutes' => 60,
            'shutdown_minutes' => 0,
            'status_after'     => 'solved',
            'consumables'      => [['consumable_id' => $consumable->id, 'qty_used' => 4]],
            'tools'            => [['tool_id' => $tool->id]],
        ]);

        $response->assertRedirect();
        $this->assertEquals(6, $consumable->fresh()->qty_actual); // 10 - 4
        $this->assertEquals(5, $tool->fresh()->qty_available);    // unchanged (log-only)
        $this->assertDatabaseHas('maintenance_record_consumables', [
            'consumable_id' => $consumable->id, 'qty_used' => 4,
        ]);
        $this->assertDatabaseHas('maintenance_record_tools', [
            'tool_id' => $tool->id,
        ]);
    }
}
