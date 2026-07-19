<?php

namespace Tests\Feature;

use App\Models\Consumable;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceRecordConsumable;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testForMonthAggregatesConsumableUsage()
    {
        $consumable = Consumable::create([
            'item_code' => 'C-1', 'name' => 'Oli', 'unit' => 'liter',
            'qty_actual' => 10, 'qty_minimum' => 0, 'unit_price' => 50000,
        ]);
        $record = MaintenanceRecord::create([
            'record_number' => 'MR-TEST-0001',
            'maintenance_date' => '2026-07-10',
            'technician_id' => 1,
            'duration_minutes' => 30, 'shutdown_minutes' => 0,
            'status_after' => 'solved',
        ]);
        MaintenanceRecordConsumable::create([
            'maintenance_record_id' => $record->id,
            'consumable_id' => $consumable->id,
            'qty_used' => 4, 'unit_price' => 50000,
        ]);

        $data = ReportService::forMonth(2026, 7);

        $this->assertCount(1, $data['records']);
        $this->assertCount(1, $data['consumables']);
        $this->assertEquals(4, $data['consumables'][0]['qty']);
        $this->assertEquals(200000, $data['consumables'][0]['value']);
    }

    /** @test */
    public function testForMonthExcludesOtherMonths()
    {
        MaintenanceRecord::create([
            'record_number' => 'MR-TEST-0002',
            'maintenance_date' => '2026-06-10',
            'technician_id' => 1,
            'duration_minutes' => 30, 'shutdown_minutes' => 0,
            'status_after' => 'solved',
        ]);

        $data = ReportService::forMonth(2026, 7);

        $this->assertCount(0, $data['records']);
    }
}
