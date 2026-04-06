<?php

namespace Database\Seeders;

use App\Models\MaintenanceSchedule;
use App\Models\ScheduleChecklistItem;
use Illuminate\Database\Seeder;

class MaintenanceScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Helper: buat array planned_weeks [{month, week}]
        $weeks = fn(array $pairs) => collect($pairs)->map(fn($p) => ['month' => $p[0], 'week' => $p[1]])->values()->all();

        $schedules = [
            // ─── PV MODULE ────────────────────────────────────────────────
            [
                'asset_id' => 1,
                'category' => 'PV Module',
                'equipment_name' => 'PV Module Area 01',
                'item_pekerjaan' => 'Pengecekan kondisi & kebersihan PV Module',
                'title' => 'Mingguan PV Module Area 01',
                'type' => 'mingguan',
                'frequency' => 'weekly',
                'planned_weeks' => $weeks([
                    [1,1],[1,3],[2,1],[2,3],[3,1],[3,3],[4,1],[4,3],
                    [5,1],[5,3],[6,1],[6,3],[7,1],[7,3],[8,1],[8,3],
                    [9,1],[9,3],[10,1],[10,3],[11,1],[11,3],[12,1],[12,3],
                ]),
                'shutdown_required' => false,
                'shutdown_duration_hours' => null,
                'status' => 'active',
                'start_date' => '2026-01-01',
                'next_due_date' => $now->copy()->addDays(3)->toDateString(),
            ],
            [
                'asset_id' => 1,
                'category' => 'PV Module',
                'equipment_name' => 'PV Module Area 01',
                'item_pekerjaan' => 'Measurement Tegangan & Arus DC Cable',
                'title' => 'Bulanan PV Module Area 01',
                'type' => 'bulanan',
                'frequency' => 'monthly',
                'planned_weeks' => $weeks([
                    [1,2],[2,2],[3,2],[4,2],[5,2],[6,2],
                    [7,2],[8,2],[9,2],[10,2],[11,2],[12,2],
                ]),
                'shutdown_required' => false,
                'shutdown_duration_hours' => null,
                'status' => 'active',
                'start_date' => '2026-01-01',
                'next_due_date' => $now->copy()->addDays(7)->toDateString(),
            ],
            // ─── INVERTER ────────────────────────────────────────────────
            [
                'asset_id' => 2,
                'category' => 'Inverter',
                'equipment_name' => 'Inverter 01',
                'item_pekerjaan' => 'Kebersihan & Thermal Check MC4 Inverter',
                'title' => 'Mingguan Inverter 01',
                'type' => 'mingguan',
                'frequency' => 'weekly',
                'planned_weeks' => $weeks([
                    [1,2],[1,4],[2,2],[2,4],[3,2],[3,4],[4,2],[4,4],
                    [5,2],[5,4],[6,2],[6,4],[7,2],[7,4],[8,2],[8,4],
                    [9,2],[9,4],[10,2],[10,4],[11,2],[11,4],[12,2],[12,4],
                ]),
                'shutdown_required' => false,
                'status' => 'active',
                'start_date' => '2026-01-01',
                'next_due_date' => $now->copy()->addDays(2)->toDateString(),
            ],
            [
                'asset_id' => 2,
                'category' => 'Inverter',
                'equipment_name' => 'Inverter 01',
                'item_pekerjaan' => 'Checking Fan, Inlet & Outlet Air Inverter',
                'title' => 'Semesteran Inverter 01',
                'type' => 'semesteran',
                'frequency' => 'quarterly',
                'planned_weeks' => $weeks([[6,2],[12,2]]),
                'shutdown_required' => true,
                'shutdown_duration_hours' => 4,
                'status' => 'active',
                'start_date' => '2026-01-01',
                'next_due_date' => '2026-06-14',
            ],
            [
                'asset_id' => 3,
                'category' => 'Inverter',
                'equipment_name' => 'Inverter 02',
                'item_pekerjaan' => 'Checking & Measuring Grounding, Thermal AC/DC Cable',
                'title' => 'Bulanan Inverter 02',
                'type' => 'bulanan',
                'frequency' => 'monthly',
                'planned_weeks' => $weeks([
                    [1,3],[2,3],[3,3],[4,3],[5,3],[6,3],
                    [7,3],[8,3],[9,3],[10,3],[11,3],[12,3],
                ]),
                'shutdown_required' => false,
                'status' => 'active',
                'start_date' => '2026-01-01',
                'next_due_date' => $now->copy()->addDays(14)->toDateString(),
            ],
            // ─── PANEL LV ────────────────────────────────────────────────
            [
                'asset_id' => 4,
                'category' => 'Panel LV',
                'equipment_name' => 'Panel LV 01',
                'item_pekerjaan' => 'Pengecekan SPD, MCCB, CB Control & UPS',
                'title' => 'Mingguan Panel LV 01',
                'type' => 'mingguan',
                'frequency' => 'weekly',
                'planned_weeks' => $weeks([
                    [1,1],[1,3],[2,1],[2,3],[3,1],[3,3],[4,1],[4,3],
                    [5,1],[5,3],[6,1],[6,3],[7,1],[7,3],[8,1],[8,3],
                    [9,1],[9,3],[10,1],[10,3],[11,1],[11,3],[12,1],[12,3],
                ]),
                'shutdown_required' => false,
                'status' => 'active',
                'start_date' => '2026-01-01',
                'next_due_date' => $now->copy()->addDays(4)->toDateString(),
            ],
            [
                'asset_id' => 4,
                'category' => 'Panel LV',
                'equipment_name' => 'Panel LV 01',
                'item_pekerjaan' => 'Thermal Monitoring AC Cable, Busbar & Material Protection',
                'title' => 'Bulanan Panel LV 01',
                'type' => 'bulanan',
                'frequency' => 'monthly',
                'planned_weeks' => $weeks([
                    [1,4],[2,4],[3,4],[4,4],[5,4],[6,4],
                    [7,4],[8,4],[9,4],[10,4],[11,4],[12,4],
                ]),
                'shutdown_required' => false,
                'status' => 'active',
                'start_date' => '2026-01-01',
                'next_due_date' => $now->copy()->addDays(20)->toDateString(),
            ],
            [
                'asset_id' => 4,
                'category' => 'Panel LV',
                'equipment_name' => 'Panel LV 01',
                'item_pekerjaan' => 'Checking Fan & Torque All Bolt',
                'title' => 'Semesteran Panel LV 01',
                'type' => 'semesteran',
                'frequency' => 'quarterly',
                'planned_weeks' => $weeks([[6,3],[12,3]]),
                'shutdown_required' => true,
                'shutdown_duration_hours' => 6,
                'status' => 'active',
                'start_date' => '2026-01-01',
                'next_due_date' => '2026-06-21',
            ],
            // ─── TRANSFORMER ─────────────────────────────────────────────
            [
                'asset_id' => 6,
                'category' => 'Transformer',
                'equipment_name' => 'Transformer 01',
                'item_pekerjaan' => 'Purifying Oil, BDV Test, DGA Test & Tightening Check',
                'title' => 'Tahunan Transformer 01',
                'type' => 'tahunan',
                'frequency' => 'annually',
                'planned_weeks' => $weeks([[3,2]]),
                'shutdown_required' => true,
                'shutdown_duration_hours' => 12,
                'status' => 'active',
                'start_date' => '2026-01-01',
                'next_due_date' => '2026-03-09',
            ],
        ];

        foreach ($schedules as $data) {
            MaintenanceSchedule::create($data);
        }
    }
}
