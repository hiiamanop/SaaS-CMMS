<?php

namespace Database\Seeders;

use App\Models\MaintenanceRecord;
use App\Models\MaintenanceRecordPart;
use App\Models\SparePart;
use Illuminate\Database\Seeder;

class MaintenanceRecordSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['record_number' => 'MR-202601-0001', 'work_order_id' => 1, 'asset_id' => 1, 'technician_id' => 3, 'type' => 'preventive', 'maintenance_date' => '2026-01-10', 'findings' => 'Oil filter clogged with debris. Oil level was 20% below minimum.', 'actions_taken' => 'Replaced oil filter. Topped up compressor oil to full level. Checked belt tension and adjusted.', 'duration_minutes' => 150, 'downtime_minutes' => 120, 'notes' => 'Next PM scheduled for February 10'],
            ['record_number' => 'MR-202601-0002', 'work_order_id' => 2, 'asset_id' => 3, 'technician_id' => 4, 'type' => 'corrective', 'maintenance_date' => '2026-01-18', 'findings' => 'Belt was misaligned by 5mm to the right causing uneven wear.', 'actions_taken' => 'Realigned belt using alignment tools. Adjusted take-up bearing. Belt tension also adjusted to spec.', 'duration_minutes' => 300, 'downtime_minutes' => 240, 'notes' => 'Monitor alignment weekly'],
            ['record_number' => 'MR-202601-0003', 'work_order_id' => 3, 'asset_id' => 6, 'technician_id' => 3, 'type' => 'preventive', 'maintenance_date' => '2026-01-25', 'findings' => 'Generator in good condition. Battery voltage slightly low at 11.8V.', 'actions_taken' => 'Ran generator for 30 minutes under load. Topped up battery water. Cleaned air filter.', 'duration_minutes' => 120, 'downtime_minutes' => 0, 'notes' => 'Battery may need replacement in 3 months'],
            ['record_number' => 'MR-202601-0004', 'work_order_id' => 16, 'asset_id' => 9, 'technician_id' => 3, 'type' => 'corrective', 'maintenance_date' => '2026-01-11', 'findings' => 'High pressure hydraulic hose burst near coupling. Oil spill on floor.', 'actions_taken' => 'Replaced burst hose with new 1/2 inch hydraulic hose. Tightened all fittings. Cleaned oil spill. Tested at full pressure.', 'duration_minutes' => 300, 'downtime_minutes' => 300, 'notes' => 'All hydraulic hoses to be inspected monthly'],
            ['record_number' => 'MR-202601-0005', 'work_order_id' => 17, 'asset_id' => 5, 'technician_id' => 4, 'type' => 'corrective', 'maintenance_date' => '2026-01-27', 'findings' => 'Front left and right tires worn to 5mm tread depth, below 8mm minimum.', 'actions_taken' => 'Replaced both front tires. Checked wheel bolts torque. Tested forklift load handling.', 'duration_minutes' => 180, 'downtime_minutes' => 180, 'notes' => 'Rear tires at 50% life, monitor'],
            ['record_number' => 'MR-202602-0001', 'work_order_id' => 4, 'asset_id' => 2, 'technician_id' => 4, 'type' => 'corrective', 'maintenance_date' => '2026-02-04', 'findings' => 'Spindle bearing completely failed. Excessive play and noise. Bearing seized.', 'actions_taken' => 'Replaced spindle bearing. Cleaned spindle housing. Applied fresh grease. Calibrated spindle runout.', 'duration_minutes' => 720, 'downtime_minutes' => 720, 'notes' => 'Machine back to spec. Monitor vibration weekly'],
            ['record_number' => 'MR-202602-0002', 'work_order_id' => 5, 'asset_id' => 5, 'technician_id' => 3, 'type' => 'preventive', 'maintenance_date' => '2026-02-10', 'findings' => 'Battery water level slightly low in cells 3 and 5. All other cells normal.', 'actions_taken' => 'Topped up distilled water in cells 3 and 5. Checked charging system. Cleaned battery terminals.', 'duration_minutes' => 180, 'downtime_minutes' => 60, 'notes' => 'Battery capacity at 85%, good'],
            ['record_number' => 'MR-202602-0003', 'work_order_id' => 18, 'asset_id' => 4, 'technician_id' => 3, 'type' => 'preventive', 'maintenance_date' => '2026-02-18', 'findings' => 'Condenser coils had heavy fouling reducing airflow by approximately 30%.', 'actions_taken' => 'Pressure washed condenser coils. Straightened bent fins. Verified airflow improvement.', 'duration_minutes' => 420, 'downtime_minutes' => 240, 'notes' => 'Schedule monthly cleaning during summer'],
            ['record_number' => 'MR-202602-0004', 'work_order_id' => 19, 'asset_id' => 3, 'technician_id' => 4, 'type' => 'preventive', 'maintenance_date' => '2026-02-24', 'findings' => 'PLC backup battery at 2.8V, below 3.0V minimum threshold.', 'actions_taken' => 'Replaced CR2032 battery in all 4 PLCs. Backed up all PLC programs before replacement. Verified backup integrity.', 'duration_minutes' => 60, 'downtime_minutes' => 15, 'notes' => 'Next battery replacement due Feb 2027'],
            ['record_number' => 'MR-202603-0001', 'work_order_id' => 7, 'asset_id' => 8, 'technician_id' => 3, 'type' => 'preventive', 'maintenance_date' => '2026-03-05', 'findings' => 'Wire rope in good condition, no broken wires. Hook latch spring slightly weak.', 'actions_taken' => 'Lubricated wire rope with crane lubricant. Replaced hook latch spring. Tested all limit switches.', 'duration_minutes' => 180, 'downtime_minutes' => 120, 'notes' => 'Wire rope at 60% life'],
            ['record_number' => 'MR-202603-0002', 'work_order_id' => 8, 'asset_id' => 4, 'technician_id' => 4, 'type' => 'corrective', 'maintenance_date' => '2026-03-12', 'findings' => 'Refrigerant R410A level 15% below nominal. Cooling capacity reduced.', 'actions_taken' => 'Topped up refrigerant to specification. Checked for leaks using leak detector. No leaks found. System operating at 100% capacity.', 'duration_minutes' => 480, 'downtime_minutes' => 360, 'notes' => 'Check refrigerant levels quarterly'],
        ];

        foreach ($records as $record) {
            MaintenanceRecord::create($record);
        }

        // Add parts used in maintenance records
        $parts = [
            ['maintenance_record_id' => 1, 'spare_part_id' => 1, 'qty_used' => 1, 'unit_price' => 150000],
            ['maintenance_record_id' => 1, 'spare_part_id' => 7, 'qty_used' => 1, 'unit_price' => 120000],
            ['maintenance_record_id' => 2, 'spare_part_id' => 2, 'qty_used' => 2, 'unit_price' => 75000],
            ['maintenance_record_id' => 4, 'spare_part_id' => 16, 'qty_used' => 2, 'unit_price' => 75000],
            ['maintenance_record_id' => 6, 'spare_part_id' => 3, 'qty_used' => 2, 'unit_price' => 45000],
            ['maintenance_record_id' => 6, 'spare_part_id' => 12, 'qty_used' => 1, 'unit_price' => 65000],
            ['maintenance_record_id' => 9, 'spare_part_id' => 14, 'qty_used' => 4, 'unit_price' => 25000],
            ['maintenance_record_id' => 10, 'spare_part_id' => 12, 'qty_used' => 1, 'unit_price' => 65000],
        ];

        foreach ($parts as $part) {
            // Deduct stock
            $sparePart = SparePart::find($part['spare_part_id']);
            $sparePart->decrement('qty_actual', $part['qty_used']);
            MaintenanceRecordPart::create($part);
        }
    }
}
