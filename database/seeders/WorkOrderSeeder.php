<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderChecklistItem;
use App\Models\WorkOrderActivityLog;
use Illuminate\Database\Seeder;

class WorkOrderSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'wakwaw@gmail.com')->first() ?? User::where('role', 'admin')->first() ?? User::first();
        $spv = User::where('role', 'supervisor')->first() ?? $admin;
        $techs = User::where('role', 'technician')->pluck('id')->all();
        $tech1 = $techs[0] ?? $admin->id;
        $tech2 = $techs[1] ?? $tech1;

        $pvModule = Asset::where('category', 'PV Module')->first()?->id ?? 1;
        $pvModule2 = Asset::where('category', 'PV Module')->skip(10)->first()?->id ?? $pvModule;
        $inverter1 = Asset::where('category', 'Inverter')->first()?->id ?? $pvModule;
        $inverter2 = Asset::where('category', 'Inverter')->skip(1)->first()?->id ?? $inverter1;
        $transformer = Asset::where('category', 'Transformer')->first()?->id ?? $pvModule;
        $metering = Asset::where('category', 'Metering')->first()?->id ?? $pvModule;

        // Semua work order = CORRECTIVE & PREVENTIVE
        $orders = [
            // ── JANUARI 2026 ──────────────────────────────────────────────
            [
                'wo_number'   => 'WO-202601-0001',
                'title'       => 'Perbaikan Kabel DC String 03 Putus',
                'asset_id'    => $pvModule,
                'assigned_to' => $tech1,
                'created_by'  => $spv->id,
                'type'        => 'corrective',
                'priority'    => 'high',
                'status'      => 'closed',
                'due_date'    => '2026-01-12',
                'started_at'  => '2026-01-10 08:00:00',
                'completed_at'=> '2026-01-10 13:00:00',
                'description' => 'Kabel DC string 03 ditemukan putus saat inspeksi mingguan, output berkurang 15%.',
            ],
            [
                'wo_number'   => 'WO-202601-0002',
                'title'       => 'Penggantian MC4 Connector Terbakar String 07',
                'asset_id'    => $pvModule2,
                'assigned_to' => $tech2,
                'created_by'  => $spv->id,
                'type'        => 'corrective',
                'priority'    => 'high',
                'status'      => 'closed',
                'due_date'    => '2026-01-18',
                'started_at'  => '2026-01-16 09:00:00',
                'completed_at'=> '2026-01-16 14:30:00',
                'description' => 'MC4 connector string 07 terbakar akibat overheating, perlu penggantian segera.',
            ],
            [
                'wo_number'   => 'WO-202601-0003',
                'title'       => 'Fan Inverter 01 Mati',
                'asset_id'    => 4,          // Inverter 01
                'assigned_to' => 3,
                'created_by'  => 2,
                'type'        => 'corrective',
                'priority'    => 'high',
                'status'      => 'closed',
                'due_date'    => '2026-01-22',
                'started_at'  => '2026-01-21 07:00:00',
                'completed_at'=> '2026-01-21 10:00:00',
                'description' => 'Alarm overtemperature pada Inverter 01, ditemukan fan pendingin mati.',
            ],

            // ── FEBRUARI 2026 ─────────────────────────────────────────────
            [
                'wo_number'   => 'WO-202602-0001',
                'title'       => 'SPD DC Panel String Box Rusak',
                'asset_id'    => 1,          // PV Module Area 01 (string combiner)
                'assigned_to' => 4,
                'created_by'  => 2,
                'type'        => 'corrective',
                'priority'    => 'medium',
                'status'      => 'closed',
                'due_date'    => '2026-02-08',
                'started_at'  => '2026-02-06 09:00:00',
                'completed_at'=> '2026-02-06 12:00:00',
                'description' => 'SPD DC pada string combiner box Area 01 sudah melampaui batas indikator merah.',
            ],
            [
                'wo_number'   => 'WO-202602-0002',
                'title'       => 'Grounding Inverter 02 Terlepas',
                'asset_id'    => 5,          // Inverter 02
                'assigned_to' => 3,
                'created_by'  => 1,          // Admin
                'type'        => 'corrective',
                'priority'    => 'critical',
                'status'      => 'closed',
                'due_date'    => '2026-02-14',
                'started_at'  => '2026-02-13 06:00:00',
                'completed_at'=> '2026-02-13 09:30:00',
                'description' => 'Kabel grounding Inverter 02 terlepas dari terminal, risiko tegangan lebih.',
            ],
            [
                'wo_number'   => 'WO-202602-0003',
                'title'       => 'MCCB Panel LV 01 Trip Tidak Bisa Reset',
                'asset_id'    => 7,          // Panel LV 01
                'assigned_to' => 4,
                'created_by'  => 2,
                'type'        => 'corrective',
                'priority'    => 'high',
                'status'      => 'closed',
                'due_date'    => '2026-02-20',
                'started_at'  => '2026-02-19 08:00:00',
                'completed_at'=> '2026-02-19 15:00:00',
                'description' => 'MCCB 250A pada Panel LV 01 trip dan tidak bisa di-reset, indikasi internal fault.',
            ],

            // ── MARET 2026 ────────────────────────────────────────────────
            [
                'wo_number'   => 'WO-202603-0001',
                'title'       => 'Kebocoran Minyak Transformer 01',
                'asset_id'    => 9,          // Transformer 01
                'assigned_to' => 3,
                'created_by'  => 2,
                'type'        => 'corrective',
                'priority'    => 'high',
                'status'      => 'closed',
                'due_date'    => '2026-03-10',
                'started_at'  => '2026-03-08 07:00:00',
                'completed_at'=> '2026-03-09 17:00:00',
                'description' => 'Ditemukan kebocoran minyak pada gasket conservator Transformer 01 saat inspeksi bulanan.',
            ],
            [
                'wo_number'   => 'WO-202603-0002',
                'title'       => 'Busbar Panel LV 01 Gosong (Loose Connection)',
                'asset_id'    => 7,          // Panel LV 01
                'assigned_to' => 4,
                'created_by'  => 2,
                'type'        => 'corrective',
                'priority'    => 'critical',
                'status'      => 'closed',
                'due_date'    => '2026-03-18',
                'started_at'  => '2026-03-17 06:00:00',
                'completed_at'=> '2026-03-17 14:00:00',
                'description' => 'Busbar Panel LV 01 ditemukan gosong akibat loose connection saat thermal imaging.',
            ],
            [
                'wo_number'   => 'WO-202603-0003',
                'title'       => 'Filter Udara Inverter 02 Tersumbat',
                'asset_id'    => 5,          // Inverter 02
                'assigned_to' => 3,
                'created_by'  => 2,
                'type'        => 'corrective',
                'priority'    => 'medium',
                'status'      => 'closed',
                'due_date'    => '2026-03-25',
                'started_at'  => '2026-03-24 09:00:00',
                'completed_at'=> '2026-03-24 11:00:00',
                'description' => 'Alarm overtemperature Inverter 02. Ditemukan filter udara tersumbat debu parah.',
            ],

            // ── APRIL 2026 (sebagian masih open/in_progress) ──────────────
            [
                'wo_number'   => 'WO-202604-0001',
                'title'       => 'Inverter 03 Trip — Output Nol',
                'asset_id'    => 6,          // Inverter 03
                'assigned_to' => 4,
                'created_by'  => 1,
                'type'        => 'corrective',
                'priority'    => 'critical',
                'status'      => 'in_progress',
                'due_date'    => '2026-04-10',
                'started_at'  => '2026-04-08 07:00:00',
                'description' => 'Inverter 03 trip mendadak pagi hari, output nol. SCADA menunjukkan fault code E108 (IGBT fault).',
            ],
            [
                'wo_number'   => 'WO-202604-0002',
                'title'       => 'Kabel Grounding PV Module Area 03 Korosi',
                'asset_id'    => 3,          // PV Module Area 03
                'assigned_to' => 3,
                'created_by'  => 2,
                'type'        => 'corrective',
                'priority'    => 'medium',
                'status'      => 'open',
                'due_date'    => '2026-04-15',
                'description' => 'Kabel grounding PV Module Area 03 ditemukan terkorosi pada beberapa titik, resistansi di atas batas.',
            ],
            [
                'wo_number'   => 'WO-202604-0003',
                'title'       => 'Baterai UPS Panel LV 02 Lemah',
                'asset_id'    => 8,          // Panel LV 02
                'assigned_to' => 4,
                'created_by'  => 2,
                'type'        => 'corrective',
                'priority'    => 'medium',
                'status'      => 'open',
                'due_date'    => '2026-04-18',
                'description' => 'Alarm UPS Panel LV 02 aktif. Uji kapasitas baterai menunjukkan hanya 40% dari nominal.',
            ],
            [
                'wo_number'   => 'WO-202604-0004',
                'title'       => 'SPD AC Panel LV 01 Indikator Merah',
                'asset_id'    => 7,          // Panel LV 01
                'assigned_to' => null,
                'assigned_to_external' => 'PT. Mitra Energi Surya',
                'created_by'  => 2,
                'type'        => 'corrective',
                'priority'    => 'high',
                'status'      => 'open',
                'due_date'    => '2026-04-20',
                'description' => 'SPD AC Type II Panel LV 01 menunjukkan indikator merah setelah petir kemarin. Perlu penggantian.',
            ],
            [
                'wo_number'   => 'WO-202604-0005',
                'title'       => 'Kebocoran Minyak Transformer 02',
                'asset_id'    => $transformer,
                'assigned_to' => $tech1,
                'created_by'  => $spv->id,
                'type'        => 'corrective',
                'priority'    => 'high',
                'status'      => 'pending_review',
                'due_date'    => '2026-04-12',
                'started_at'  => '2026-04-07 08:00:00',
                'completed_at'=> '2026-04-07 16:00:00',
                'description' => 'Kebocoran minyak Transformer 02 pada seal radiator. Sudah diperbaiki, menunggu review SPV.',
            ],

            // ── MEI 2026 ──────────────────────────────────────────────────
            [
                'wo_number'   => 'WO-202605-0001',
                'title'       => 'Pembersihan Filter Udara Inverter Blok T03',
                'asset_id'    => $inverter1,
                'assigned_to' => $tech1,
                'created_by'  => $spv->id,
                'type'        => 'preventive',
                'priority'    => 'medium',
                'status'      => 'closed',
                'due_date'    => '2026-05-15',
                'started_at'  => '2026-05-14 08:00:00',
                'completed_at'=> '2026-05-14 11:30:00',
                'description' => 'Pembersihan debu filter intake inverter T03 pasca musim kemarau.',
            ],

            // ── JUNI 2026 ─────────────────────────────────────────────────
            [
                'wo_number'   => 'WO-202606-0001',
                'title'       => 'Thermovision Hotspot Check Blok T04',
                'asset_id'    => $pvModule,
                'assigned_to' => $tech2,
                'created_by'  => $spv->id,
                'type'        => 'preventive',
                'priority'    => 'medium',
                'status'      => 'closed',
                'due_date'    => '2026-06-20',
                'started_at'  => '2026-06-18 10:00:00',
                'completed_at'=> '2026-06-18 15:00:00',
                'description' => 'Inspeksi drone thermovision pada modul string T04. Ditemukan 2 hotspot minor.',
            ],

            // ── JULI 2026 ─────────────────────────────────────────────────
            [
                'wo_number'   => 'WO-202607-0001',
                'title'       => 'Penggantian Fuse DC 15A String Box T05',
                'asset_id'    => $pvModule2,
                'assigned_to' => $tech1,
                'created_by'  => $spv->id,
                'type'        => 'corrective',
                'priority'    => 'high',
                'status'      => 'closed',
                'due_date'    => '2026-07-10',
                'started_at'  => '2026-07-09 09:00:00',
                'completed_at'=> '2026-07-09 11:30:00',
                'description' => 'Fuse DC 1000V 15A putus akibat arus lebih saat radiasi puncak.',
            ],

            // ── AGUSTUS 2026 ──────────────────────────────────────────────
            [
                'wo_number'   => 'WO-202608-0001',
                'title'       => 'Inspeksi & Kalibrasi Sensor Pyranometer',
                'asset_id'    => $metering,
                'assigned_to' => $tech2,
                'created_by'  => $spv->id,
                'type'        => 'preventive',
                'priority'    => 'low',
                'status'      => 'closed',
                'due_date'    => '2026-08-15',
                'started_at'  => '2026-08-14 08:30:00',
                'completed_at'=> '2026-08-14 10:00:00',
                'description' => 'Pembersihan kubah kaca pyranometer dan kalibrasi output millivolt.',
            ],

            // ── SEPTEMBER 2026 (Aktif Berjalan) ───────────────────────────
            [
                'wo_number'   => 'WO-202609-0001',
                'title'       => 'Pemeriksaan Rutin Trafo Step-Up T01',
                'asset_id'    => $transformer,
                'assigned_to' => $tech1,
                'created_by'  => $spv->id,
                'type'        => 'preventive',
                'priority'    => 'medium',
                'status'      => 'in_progress',
                'due_date'    => now()->addDays(2)->toDateString(),
                'started_at'  => now()->subHours(2)->toDateTimeString(),
                'description' => 'Sampling minyak trafo berkala dan pengecekan suhu operasional.',
            ],
            [
                'wo_number'   => 'WO-202609-0002',
                'title'       => 'Perbaikan Konektor MC4 String 14 Blok T06',
                'asset_id'    => $pvModule,
                'assigned_to' => $tech2,
                'created_by'  => $spv->id,
                'type'        => 'corrective',
                'priority'    => 'high',
                'status'      => 'open',
                'due_date'    => now()->addDays(1)->toDateString(),
                'description' => 'Laporan temuan inspeksi visual: konektor MC4 longgar pada string 14.',
            ],
        ];

        foreach ($orders as $order) {
            // Normalize IDs to valid database records
            if (empty($order['created_by']) || !User::where('id', $order['created_by'])->exists()) {
                $order['created_by'] = $spv->id;
            }
            if (!empty($order['assigned_to']) && !User::where('id', $order['assigned_to'])->exists()) {
                $order['assigned_to'] = ($order['assigned_to'] % 2 === 0) ? $tech2 : $tech1;
            }
            if (!empty($order['asset_id']) && !Asset::where('id', $order['asset_id'])->exists()) {
                $order['asset_id'] = $inverter1;
            }
            WorkOrder::create($order);
        }

        // Activity Logs
        $logs = [
            // WO-202601-0001 (closed)
            ['work_order_id' => 1, 'user_id' => $tech1, 'from_status' => 'open',        'to_status' => 'in_progress', 'notes' => 'Mulai perbaikan, kabel DC string 03 dilepas untuk diganti.',         'created_at' => '2026-01-10 08:00:00'],
            ['work_order_id' => 1, 'user_id' => $tech1, 'from_status' => 'in_progress', 'to_status' => 'closed',      'notes' => 'Kabel DC diganti, output string kembali normal. Uji tegangan OK.',   'created_at' => '2026-01-10 13:00:00'],

            // WO-202601-0002 (closed)
            ['work_order_id' => 2, 'user_id' => $tech2, 'from_status' => 'open',        'to_status' => 'in_progress', 'notes' => 'Mulai penggantian MC4 connector string 07.',                          'created_at' => '2026-01-16 09:00:00'],
            ['work_order_id' => 2, 'user_id' => $tech2, 'from_status' => 'in_progress', 'to_status' => 'closed',      'notes' => '8 pasang MC4 connector diganti. Output string kembali normal.',        'created_at' => '2026-01-16 14:30:00'],

            // WO-202601-0003 (closed)
            ['work_order_id' => 3, 'user_id' => $tech1, 'from_status' => 'open',        'to_status' => 'in_progress', 'notes' => 'Inverter 01 dimatikan. Mulai pembongkaran fan pendingin.',            'created_at' => '2026-01-21 07:00:00'],
            ['work_order_id' => 3, 'user_id' => $tech1, 'from_status' => 'in_progress', 'to_status' => 'closed',      'notes' => 'Fan baru terpasang. Suhu normal kembali ke 42°C.',                     'created_at' => '2026-01-21 10:00:00'],
        ];

        foreach ($logs as $log) {
            if (!User::where('id', $log['user_id'])->exists()) {
                $log['user_id'] = $tech1;
            }
            WorkOrderActivityLog::create($log);
        }

        // Checklist items untuk WO yang masih open/in_progress
        $checklists = [
            // WO-202604-0001 (Inverter 03 Trip)
            ['work_order_id' => 10, 'description' => 'Matikan inverter & lock out / tag out',           'is_checked' => true,  'checked_by' => $tech2, 'checked_at' => '2026-04-08 07:10:00', 'order' => 1],
            ['work_order_id' => 10, 'description' => 'Ukur tegangan DC input & AC output',              'is_checked' => true,  'checked_by' => $tech2, 'checked_at' => '2026-04-08 07:30:00', 'order' => 2],
            ['work_order_id' => 10, 'description' => 'Baca fault code & download log inverter',         'is_checked' => true,  'checked_by' => $tech2, 'checked_at' => '2026-04-08 08:00:00', 'order' => 3],
            ['work_order_id' => 10, 'description' => 'Periksa kondisi IGBT module',                    'is_checked' => false, 'order' => 4],
            ['work_order_id' => 10, 'description' => 'Ganti komponen yang rusak & uji coba inverter',  'is_checked' => false, 'order' => 5],

            // WO-202604-0002 (Grounding PV Area 03)
            ['work_order_id' => 11, 'description' => 'Identifikasi titik-titik kabel grounding korosi', 'is_checked' => false, 'order' => 1],
            ['work_order_id' => 11, 'description' => 'Lepas kabel grounding yang terkorosi',            'is_checked' => false, 'order' => 2],
            ['work_order_id' => 11, 'description' => 'Pasang kabel grounding baru 16mm²',               'is_checked' => false, 'order' => 3],
            ['work_order_id' => 11, 'description' => 'Uji resistansi grounding (< 1 ohm)',              'is_checked' => false, 'order' => 4],
        ];

        foreach ($checklists as $cl) {
            if (!empty($cl['checked_by']) && !User::where('id', $cl['checked_by'])->exists()) {
                $cl['checked_by'] = $tech1;
            }
            WorkOrderChecklistItem::create($cl);
        }
    }
}
