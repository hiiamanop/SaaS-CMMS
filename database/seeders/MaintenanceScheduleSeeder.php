<?php

namespace Database\Seeders;

use App\Models\MaintenanceSchedule;
use Illuminate\Database\Seeder;

class MaintenanceScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Helper: buat array planned_weeks dari pasangan [month, week]
        $weeks = fn(array $pairs) => collect($pairs)
            ->map(fn($p) => ['month' => $p[0], 'week' => $p[1]])
            ->values()->all();

        // technician_id: 3=Andi (technician), 4=Rudi (technician)
        $schedules = [

            // ═══════════════════════════════════════════════
            // PV MODULE AREA 01  (asset_id=1)
            // ═══════════════════════════════════════════════
            [
                'asset_id'       => 1,
                'technician_id'  => 3,
                'category'       => 'PV Module',
                'equipment_name' => 'PV Module Area 01',
                'item_pekerjaan' => 'Pengecekan kondisi & kebersihan PV Module (visual, debu, kerusakan fisik)',
                'title'          => 'Mingguan — PV Module Area 01',
                'type'           => 'preventive',
                'frequency'      => 'weekly',
                'planned_weeks'  => $weeks([
                    [1,1],[1,3],[2,1],[2,3],[3,1],[3,3],[4,1],[4,3],
                    [5,1],[5,3],[6,1],[6,3],[7,1],[7,3],[8,1],[8,3],
                    [9,1],[9,3],[10,1],[10,3],[11,1],[11,3],[12,1],[12,3],
                ]),
                'shutdown_required'       => false,
                'shutdown_duration_hours' => null,
                'status'                  => 'active',
                'start_date'              => '2026-01-01',
                'next_due_date'           => $now->copy()->addDays(3)->toDateString(),
            ],
            // asset 1: PV Module 01 (monthly)
            ['asset_id'=>1,'technician_id'=>3,'category'=>'PV Module','equipment_name'=>'PV Module Area 01',
             'item_pekerjaan'=>'Measurement tegangan & arus DC per string, pengecekan MC4 connector',
             'title'=>'Bulanan — PV Module Area 01','type'=>'preventive','frequency'=>'monthly',
             'planned_weeks'=>$weeks([[1,2],[2,2],[3,2],[4,2],[5,2],[6,2],[7,2],[8,2],[9,2],[10,2],[11,2],[12,2]]),
             'shutdown_required'=>false,'shutdown_duration_hours'=>null,'status'=>'active',
             'start_date'=>'2026-01-01','next_due_date'=>$now->copy()->addDays(8)->toDateString()],

            // asset 1: PV Module 01 (semesteran)
            ['asset_id'=>1,'technician_id'=>3,'category'=>'PV Module','equipment_name'=>'PV Module Area 01',
             'item_pekerjaan'=>'Pembersihan menyeluruh modul, pengecekan grounding & struktur mounting',
             'title'=>'Semesteran — PV Module Area 01','type'=>'preventive','frequency'=>'quarterly',
             'planned_weeks'=>$weeks([[6,1],[12,1]]),
             'shutdown_required'=>false,'shutdown_duration_hours'=>null,'status'=>'active',
             'start_date'=>'2026-01-01','next_due_date'=>'2026-06-07'],

            // asset 2: PV Module 02 (mingguan)
            ['asset_id'=>2,'technician_id'=>4,'category'=>'PV Module','equipment_name'=>'PV Module Area 02',
             'item_pekerjaan'=>'Pengecekan kondisi & kebersihan PV Module (visual, debu, kerusakan fisik)',
             'title'=>'Mingguan — PV Module Area 02','type'=>'preventive','frequency'=>'weekly',
             'planned_weeks'=>$weeks([[1,2],[1,4],[2,2],[2,4],[3,2],[3,4],[4,2],[4,4],[5,2],[5,4],[6,2],[6,4],[7,2],[7,4],[8,2],[8,4],[9,2],[9,4],[10,2],[10,4],[11,2],[11,4],[12,2],[12,4]]),
             'shutdown_required'=>false,'shutdown_duration_hours'=>null,'status'=>'active',
             'start_date'=>'2026-01-01','next_due_date'=>$now->copy()->addDays(5)->toDateString()],

            // asset 4: Inverter 01 (mingguan)
            ['asset_id'=>4,'technician_id'=>3,'category'=>'Inverter','equipment_name'=>'Inverter 01',
             'item_pekerjaan'=>'Pengecekan kebersihan, thermal check MC4 & kabel AC/DC, cek display & alarm',
             'title'=>'Mingguan — Inverter 01','type'=>'preventive','frequency'=>'weekly',
             'planned_weeks'=>$weeks([[1,1],[1,3],[2,1],[2,3],[3,1],[3,3],[4,1],[4,3],[5,1],[5,3],[6,1],[6,3],[7,1],[7,3],[8,1],[8,3],[9,1],[9,3],[10,1],[10,3],[11,1],[11,3],[12,1],[12,3]]),
             'shutdown_required'=>false,'shutdown_duration_hours'=>null,'status'=>'active',
             'start_date'=>'2026-01-01','next_due_date'=>$now->copy()->addDays(2)->toDateString()],

            // asset 4: Inverter 01 (bulanan)
            ['asset_id'=>4,'technician_id'=>3,'category'=>'Inverter','equipment_name'=>'Inverter 01',
             'item_pekerjaan'=>'Checking fan, filter udara inlet & outlet, pengukuran grounding, torque terminal',
             'title'=>'Bulanan — Inverter 01','type'=>'preventive','frequency'=>'monthly',
             'planned_weeks'=>$weeks([[1,4],[2,4],[3,4],[4,4],[5,4],[6,4],[7,4],[8,4],[9,4],[10,4],[11,4],[12,4]]),
             'shutdown_required'=>false,'shutdown_duration_hours'=>null,'status'=>'active',
             'start_date'=>'2026-01-01','next_due_date'=>$now->copy()->addDays(18)->toDateString()],

            // asset 4: Inverter 01 (semesteran)
            ['asset_id'=>4,'technician_id'=>3,'category'=>'Inverter','equipment_name'=>'Inverter 01',
             'item_pekerjaan'=>'Overhaul inverter: bersihkan board, cek kapasitor, uji insulation resistance, update firmware',
             'title'=>'Semesteran — Inverter 01','type'=>'preventive','frequency'=>'quarterly',
             'planned_weeks'=>$weeks([[6,2],[12,2]]),
             'shutdown_required'=>true,'shutdown_duration_hours'=>4,'status'=>'active',
             'start_date'=>'2026-01-01','next_due_date'=>'2026-06-14'],

            // asset 5: Inverter 02 (mingguan)
            ['asset_id'=>5,'technician_id'=>4,'category'=>'Inverter','equipment_name'=>'Inverter 02',
             'item_pekerjaan'=>'Pengecekan kebersihan, thermal check MC4 & kabel AC/DC, cek display & alarm',
             'title'=>'Mingguan — Inverter 02','type'=>'preventive','frequency'=>'weekly',
             'planned_weeks'=>$weeks([[1,2],[1,4],[2,2],[2,4],[3,2],[3,4],[4,2],[4,4],[5,2],[5,4],[6,2],[6,4],[7,2],[7,4],[8,2],[8,4],[9,2],[9,4],[10,2],[10,4],[11,2],[11,4],[12,2],[12,4]]),
             'shutdown_required'=>false,'shutdown_duration_hours'=>null,'status'=>'active',
             'start_date'=>'2026-01-01','next_due_date'=>$now->copy()->addDays(4)->toDateString()],

            // asset 5: Inverter 02 (bulanan)
            ['asset_id'=>5,'technician_id'=>4,'category'=>'Inverter','equipment_name'=>'Inverter 02',
             'item_pekerjaan'=>'Checking & measuring grounding, thermal AC/DC cable, pengecekan proteksi',
             'title'=>'Bulanan — Inverter 02','type'=>'preventive','frequency'=>'monthly',
             'planned_weeks'=>$weeks([[1,3],[2,3],[3,3],[4,3],[5,3],[6,3],[7,3],[8,3],[9,3],[10,3],[11,3],[12,3]]),
             'shutdown_required'=>false,'shutdown_duration_hours'=>null,'status'=>'active',
             'start_date'=>'2026-01-01','next_due_date'=>$now->copy()->addDays(12)->toDateString()],

            // asset 7: Panel LV 01 (mingguan)
            ['asset_id'=>7,'technician_id'=>4,'category'=>'Panel LV','equipment_name'=>'Panel LV 01',
             'item_pekerjaan'=>'Pengecekan SPD, MCCB, CB control, UPS & lampu indikator panel',
             'title'=>'Mingguan — Panel LV 01','type'=>'preventive','frequency'=>'weekly',
             'planned_weeks'=>$weeks([[1,1],[1,3],[2,1],[2,3],[3,1],[3,3],[4,1],[4,3],[5,1],[5,3],[6,1],[6,3],[7,1],[7,3],[8,1],[8,3],[9,1],[9,3],[10,1],[10,3],[11,1],[11,3],[12,1],[12,3]]),
             'shutdown_required'=>false,'shutdown_duration_hours'=>null,'status'=>'active',
             'start_date'=>'2026-01-01','next_due_date'=>$now->copy()->addDays(1)->toDateString()],

            // asset 7: Panel LV 01 (bulanan)
            ['asset_id'=>7,'technician_id'=>4,'category'=>'Panel LV','equipment_name'=>'Panel LV 01',
             'item_pekerjaan'=>'Thermal monitoring AC cable, busbar & material proteksi, pengecekan fuse',
             'title'=>'Bulanan — Panel LV 01','type'=>'preventive','frequency'=>'monthly',
             'planned_weeks'=>$weeks([[1,4],[2,4],[3,4],[4,4],[5,4],[6,4],[7,4],[8,4],[9,4],[10,4],[11,4],[12,4]]),
             'shutdown_required'=>false,'shutdown_duration_hours'=>null,'status'=>'active',
             'start_date'=>'2026-01-01','next_due_date'=>$now->copy()->addDays(20)->toDateString()],

            // asset 7: Panel LV 01 (semesteran)
            ['asset_id'=>7,'technician_id'=>4,'category'=>'Panel LV','equipment_name'=>'Panel LV 01',
             'item_pekerjaan'=>'Torque all bolt & busbar, pembersihan menyeluruh, uji trip MCCB',
             'title'=>'Semesteran — Panel LV 01','type'=>'preventive','frequency'=>'quarterly',
             'planned_weeks'=>$weeks([[6,3],[12,3]]),
             'shutdown_required'=>true,'shutdown_duration_hours'=>6,'status'=>'active',
             'start_date'=>'2026-01-01','next_due_date'=>'2026-06-21'],

            // asset 9: Transformer 01 (bulanan)
            ['asset_id'=>9,'technician_id'=>3,'category'=>'Transformer','equipment_name'=>'Transformer 01',
             'item_pekerjaan'=>'Pengecekan level minyak, suhu, kebocoran & kondisi bushing secara visual',
             'title'=>'Bulanan — Transformer 01','type'=>'preventive','frequency'=>'monthly',
             'planned_weeks'=>$weeks([[1,2],[2,2],[3,2],[4,2],[5,2],[6,2],[7,2],[8,2],[9,2],[10,2],[11,2],[12,2]]),
             'shutdown_required'=>false,'shutdown_duration_hours'=>null,'status'=>'active',
             'start_date'=>'2026-01-01','next_due_date'=>$now->copy()->addDays(10)->toDateString()],

            // asset 9: Transformer 01 (tahunan)
            ['asset_id'=>9,'technician_id'=>3,'category'=>'Transformer','equipment_name'=>'Transformer 01',
             'item_pekerjaan'=>'Purifying oil, BDV test, DGA test, tightening check & uji rasio belitan',
             'title'=>'Tahunan — Transformer 01','type'=>'preventive','frequency'=>'annually',
             'planned_weeks'=>$weeks([[3,2]]),
             'shutdown_required'=>true,'shutdown_duration_hours'=>12,'status'=>'active',
             'start_date'=>'2026-01-01','next_due_date'=>'2026-03-09'],

            // asset 10: Transformer 02 (bulanan)
            ['asset_id'=>10,'technician_id'=>4,'category'=>'Transformer','equipment_name'=>'Transformer 02',
             'item_pekerjaan'=>'Pengecekan level minyak, suhu, kebocoran & kondisi bushing secara visual',
             'title'=>'Bulanan — Transformer 02','type'=>'preventive','frequency'=>'monthly',
             'planned_weeks'=>$weeks([[1,3],[2,3],[3,3],[4,3],[5,3],[6,3],[7,3],[8,3],[9,3],[10,3],[11,3],[12,3]]),
             'shutdown_required'=>false,'shutdown_duration_hours'=>null,'status'=>'active',
             'start_date'=>'2026-01-01','next_due_date'=>$now->copy()->addDays(15)->toDateString()],

            // asset 10: Transformer 02 (tahunan)
            ['asset_id'=>10,'technician_id'=>4,'category'=>'Transformer','equipment_name'=>'Transformer 02',
             'item_pekerjaan'=>'Purifying oil, BDV test, DGA test, tightening check & uji rasio belitan',
             'title'=>'Tahunan — Transformer 02','type'=>'preventive','frequency'=>'annually',
             'planned_weeks'=>$weeks([[3,3]]),
             'shutdown_required'=>true,'shutdown_duration_hours'=>12,'status'=>'active',
             'start_date'=>'2026-01-01','next_due_date'=>'2026-03-16'],
        ];

        foreach ($schedules as $data) {
            MaintenanceSchedule::create($data);
        }
    }
}
