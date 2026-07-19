<?php

namespace Tests\Feature;

use App\Models\Consumable;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceRecordConsumable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testReportPageShowsConsumableUsedThatMonth()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $consumable = Consumable::create([
            'item_code' => 'C-1', 'name' => 'OliMesin', 'unit' => 'liter',
            'qty_actual' => 10, 'qty_minimum' => 0, 'unit_price' => 50000,
        ]);
        $record = MaintenanceRecord::create([
            'record_number' => 'MR-TEST-0001', 'maintenance_date' => '2026-07-10',
            'technician_id' => $user->id, 'duration_minutes' => 30,
            'shutdown_minutes' => 0, 'status_after' => 'solved',
        ]);
        MaintenanceRecordConsumable::create([
            'maintenance_record_id' => $record->id, 'consumable_id' => $consumable->id,
            'qty_used' => 4, 'unit_price' => 50000,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index', ['year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee('OliMesin');
    }
}
