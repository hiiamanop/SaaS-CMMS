<?php

namespace Database\Seeders;

use App\Models\MaintenanceSchedule;
use App\Models\ScheduleChecklistItem;
use Illuminate\Database\Seeder;

class MaintenanceScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $schedules = [
            [
                'title' => 'Monthly PM - Air Compressor Unit A',
                'asset_id' => 1, 'type' => 'preventive', 'frequency' => 'monthly',
                'start_date' => '2024-01-01', 'next_due_date' => '2026-03-01', 'status' => 'active',
                'checklist' => ['Check oil level and top up if necessary', 'Inspect and clean air filter', 'Check belt tension', 'Inspect safety valves', 'Record operating pressure'],
            ],
            [
                'title' => 'Weekly PM - Conveyor Belt System',
                'asset_id' => 3, 'type' => 'preventive', 'frequency' => 'weekly',
                'start_date' => '2025-01-01', 'next_due_date' => '2026-03-20', 'status' => 'active',
                'checklist' => ['Inspect belt alignment', 'Check belt tension', 'Lubricate drive chain', 'Inspect rollers for wear', 'Check motor temperature'],
            ],
            [
                'title' => 'Quarterly PM - Industrial Chiller',
                'asset_id' => 4, 'type' => 'preventive', 'frequency' => 'quarterly',
                'start_date' => '2024-01-01', 'next_due_date' => '2026-01-01', 'status' => 'active',
                'checklist' => ['Inspect refrigerant levels', 'Clean condenser coils', 'Check electrical connections', 'Test safety controls', 'Record temperatures and pressures'],
            ],
            [
                'title' => 'Monthly PM - Generator Set',
                'asset_id' => 6, 'type' => 'preventive', 'frequency' => 'monthly',
                'start_date' => '2024-01-01', 'next_due_date' => '2026-02-15', 'status' => 'active',
                'checklist' => ['Check oil level', 'Test run for 30 minutes', 'Inspect fuel lines', 'Check battery voltage', 'Inspect air cleaner'],
            ],
            [
                'title' => 'Annual PM - CNC Milling Machine',
                'asset_id' => 2, 'type' => 'preventive', 'frequency' => 'annually',
                'start_date' => '2024-01-01', 'next_due_date' => '2025-12-01', 'status' => 'active',
                'checklist' => ['Full spindle inspection', 'Calibrate all axes', 'Replace way wipers', 'Check coolant system', 'Inspect tool changer mechanism'],
            ],
            [
                'title' => 'Monthly PM - Forklift Electric',
                'asset_id' => 5, 'type' => 'preventive', 'frequency' => 'monthly',
                'start_date' => '2025-01-01', 'next_due_date' => '2026-02-01', 'status' => 'active',
                'checklist' => ['Check battery water level', 'Inspect tires', 'Test horn and lights', 'Check forks for cracks', 'Lubricate mast chain'],
            ],
            [
                'title' => 'Weekly PM - Overhead Crane',
                'asset_id' => 8, 'type' => 'preventive', 'frequency' => 'weekly',
                'start_date' => '2025-01-01', 'next_due_date' => '2026-03-10', 'status' => 'active',
                'checklist' => ['Inspect wire rope for damage', 'Test limit switches', 'Lubricate hoist mechanism', 'Inspect hook and block assembly', 'Check brake operation'],
            ],
            [
                'title' => 'Monthly PM - Hydraulic Press',
                'asset_id' => 9, 'type' => 'preventive', 'frequency' => 'monthly',
                'start_date' => '2024-06-01', 'next_due_date' => '2026-01-15', 'status' => 'active',
                'checklist' => ['Check hydraulic oil level', 'Inspect hoses and fittings', 'Test pressure relief valve', 'Inspect cylinder seals', 'Check electrical panel'],
            ],
        ];

        foreach ($schedules as $data) {
            $checklist = $data['checklist'];
            unset($data['checklist']);
            $schedule = MaintenanceSchedule::create($data);
            foreach ($checklist as $i => $item) {
                ScheduleChecklistItem::create([
                    'maintenance_schedule_id' => $schedule->id,
                    'description' => $item,
                    'order' => $i + 1,
                ]);
            }
        }
    }
}
