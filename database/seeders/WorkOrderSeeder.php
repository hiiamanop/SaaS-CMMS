<?php

namespace Database\Seeders;

use App\Models\WorkOrder;
use App\Models\WorkOrderChecklistItem;
use App\Models\WorkOrderActivityLog;
use Illuminate\Database\Seeder;

class WorkOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Semua work order = CORRECTIVE
        $orders = [
            // ── JANUARI 2026 ──────────────────────────────────────────────
            [
                'wo_number'   => 'WO-202601-0001',
                'title'       => 'Perbaikan Kabel DC String 03 Putus',
                'asset_id'    => 1,          // PV Module Area 01
                'assigned_to' => 3,          // Andi Teknisi
                'created_by'  => 2,          // Budi Supervisor
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
                'asset_id'    => 2,          // PV Module Area 02
                'assigned_to' => 4,          // Rudi Teknisi
                'created_by'  => 2,
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
                'asset_id'    => 10,         // Transformer 02
                'assigned_to' => 3,
                'created_by'  => 2,
                'type'        => 'corrective',
                'priority'    => 'high',
                'status'      => 'pending_review',
                'due_date'    => '2026-04-12',
                'started_at'  => '2026-04-07 08:00:00',
                'completed_at'=> '2026-04-07 16:00:00',
                'description' => 'Kebocoran minyak Transformer 02 pada seal radiator. Sudah diperbaiki, menunggu review SPV.',
            ],
        ];

        foreach ($orders as $order) {
            WorkOrder::create($order);
        }

        // Activity Logs
        $logs = [
            // WO-202601-0001 (closed)
            ['work_order_id' => 1, 'user_id' => 3, 'from_status' => 'open',        'to_status' => 'in_progress', 'notes' => 'Mulai perbaikan, kabel DC string 03 dilepas untuk diganti.',         'created_at' => '2026-01-10 08:00:00'],
            ['work_order_id' => 1, 'user_id' => 3, 'from_status' => 'in_progress', 'to_status' => 'closed',      'notes' => 'Kabel DC diganti, output string kembali normal. Uji tegangan OK.',   'created_at' => '2026-01-10 13:00:00'],

            // WO-202601-0002 (closed)
            ['work_order_id' => 2, 'user_id' => 4, 'from_status' => 'open',        'to_status' => 'in_progress', 'notes' => 'Mulai penggantian MC4 connector string 07.',                          'created_at' => '2026-01-16 09:00:00'],
            ['work_order_id' => 2, 'user_id' => 4, 'from_status' => 'in_progress', 'to_status' => 'closed',      'notes' => '8 pasang MC4 connector diganti. Output string kembali normal.',        'created_at' => '2026-01-16 14:30:00'],

            // WO-202601-0003 (closed)
            ['work_order_id' => 3, 'user_id' => 3, 'from_status' => 'open',        'to_status' => 'in_progress', 'notes' => 'Inverter dimatikan sementara untuk penggantian fan.',                 'created_at' => '2026-01-21 07:00:00'],
            ['work_order_id' => 3, 'user_id' => 3, 'from_status' => 'in_progress', 'to_status' => 'closed',      'notes' => 'Fan inverter diganti, suhu inverter kembali normal < 45°C.',          'created_at' => '2026-01-21 10:00:00'],

            // WO-202602-0001 (closed)
            ['work_order_id' => 4, 'user_id' => 4, 'from_status' => 'open',        'to_status' => 'in_progress', 'notes' => 'SPD DC dicopot dari panel untuk diperiksa.',                          'created_at' => '2026-02-06 09:00:00'],
            ['work_order_id' => 4, 'user_id' => 4, 'from_status' => 'in_progress', 'to_status' => 'closed',      'notes' => 'SPD DC Type II diganti unit baru. Indikator hijau. Sistem normal.',    'created_at' => '2026-02-06 12:00:00'],

            // WO-202602-0002 (closed)
            ['work_order_id' => 5, 'user_id' => 3, 'from_status' => 'open',        'to_status' => 'in_progress', 'notes' => 'Sistem dimatikan sementara untuk keamanan. Mulai pemasangan kabel.',  'created_at' => '2026-02-13 06:00:00'],
            ['work_order_id' => 5, 'user_id' => 3, 'from_status' => 'in_progress', 'to_status' => 'closed',      'notes' => 'Kabel grounding disambung kembali & dikencangkan. Uji resistansi OK.', 'created_at' => '2026-02-13 09:30:00'],

            // WO-202602-0003 (closed)
            ['work_order_id' => 6, 'user_id' => 4, 'from_status' => 'open',        'to_status' => 'in_progress', 'notes' => 'Sistem dimatikan. MCCB lama dilepas untuk diganti.',                  'created_at' => '2026-02-19 08:00:00'],
            ['work_order_id' => 6, 'user_id' => 4, 'from_status' => 'in_progress', 'to_status' => 'closed',      'notes' => 'MCCB 250A baru terpasang. Uji trip & reset OK. Sistem kembali normal.', 'created_at' => '2026-02-19 15:00:00'],

            // WO-202603-0001 (closed)
            ['work_order_id' => 7, 'user_id' => 3, 'from_status' => 'open',        'to_status' => 'in_progress', 'notes' => 'Trafo dimatikan. Gasket conservator dilepas untuk diganti.',           'created_at' => '2026-03-08 07:00:00'],
            ['work_order_id' => 7, 'user_id' => 3, 'from_status' => 'in_progress', 'to_status' => 'closed',      'notes' => 'Gasket baru terpasang, minyak ditambah hingga level normal. Tidak ada kebocoran.', 'created_at' => '2026-03-09 17:00:00'],

            // WO-202603-0002 (closed)
            ['work_order_id' => 8, 'user_id' => 4, 'from_status' => 'open',        'to_status' => 'in_progress', 'notes' => 'Panel LV dimatikan total. Busbar gosong dibersihkan & diukur.',        'created_at' => '2026-03-17 06:00:00'],
            ['work_order_id' => 8, 'user_id' => 4, 'from_status' => 'in_progress', 'to_status' => 'closed',      'notes' => 'Busbar diganti, semua baut dikencangkan ulang. Uji thermal OK.',       'created_at' => '2026-03-17 14:00:00'],

            // WO-202603-0003 (closed)
            ['work_order_id' => 9, 'user_id' => 3, 'from_status' => 'open',        'to_status' => 'in_progress', 'notes' => 'Inverter 02 dimatikan. Filter udara dilepas untuk dibersihkan.',       'created_at' => '2026-03-24 09:00:00'],
            ['work_order_id' => 9, 'user_id' => 3, 'from_status' => 'in_progress', 'to_status' => 'closed',      'notes' => 'Filter diganti baru. Suhu inverter turun ke 38°C. Normal.',            'created_at' => '2026-03-24 11:00:00'],

            // WO-202604-0001 (in_progress)
            ['work_order_id' => 10,'user_id' => 4, 'from_status' => 'open',        'to_status' => 'in_progress', 'notes' => 'Inverter dimatikan. Pengecekan IGBT dan board kontrol sedang dilakukan.', 'created_at' => '2026-04-08 07:00:00'],

            // WO-202604-0005 (pending_review)
            ['work_order_id' => 13,'user_id' => 3, 'from_status' => 'open',        'to_status' => 'in_progress', 'notes' => 'Trafo 02 dimatikan. Seal radiator dilepas.',                           'created_at' => '2026-04-07 08:00:00'],
            ['work_order_id' => 13,'user_id' => 3, 'from_status' => 'in_progress', 'to_status' => 'pending_review', 'notes' => 'Seal diganti, minyak ditambah. Menunggu review & uji 24 jam.',      'created_at' => '2026-04-07 16:00:00'],
        ];

        foreach ($logs as $log) {
            WorkOrderActivityLog::create($log);
        }

        // Checklist items untuk WO yang masih open/in_progress
        $checklists = [
            // WO-202604-0001 (Inverter 03 Trip)
            ['work_order_id' => 10, 'description' => 'Matikan inverter & lock out / tag out',           'is_checked' => true,  'checked_by' => 4, 'checked_at' => '2026-04-08 07:10:00', 'order' => 1],
            ['work_order_id' => 10, 'description' => 'Ukur tegangan DC input & AC output',              'is_checked' => true,  'checked_by' => 4, 'checked_at' => '2026-04-08 07:30:00', 'order' => 2],
            ['work_order_id' => 10, 'description' => 'Baca fault code & download log inverter',         'is_checked' => true,  'checked_by' => 4, 'checked_at' => '2026-04-08 08:00:00', 'order' => 3],
            ['work_order_id' => 10, 'description' => 'Periksa kondisi IGBT module',                    'is_checked' => false, 'order' => 4],
            ['work_order_id' => 10, 'description' => 'Ganti komponen yang rusak & uji coba inverter',  'is_checked' => false, 'order' => 5],

            // WO-202604-0002 (Grounding PV Area 03)
            ['work_order_id' => 11, 'description' => 'Identifikasi titik-titik kabel grounding korosi', 'is_checked' => false, 'order' => 1],
            ['work_order_id' => 11, 'description' => 'Lepas kabel grounding yang terkorosi',            'is_checked' => false, 'order' => 2],
            ['work_order_id' => 11, 'description' => 'Pasang kabel grounding baru 16mm²',               'is_checked' => false, 'order' => 3],
            ['work_order_id' => 11, 'description' => 'Uji resistansi grounding (< 1 ohm)',              'is_checked' => false, 'order' => 4],

            // WO-202604-0003 (Baterai UPS Panel LV 02)
            ['work_order_id' => 12, 'description' => 'Matikan UPS & lepas baterai lama',                'is_checked' => false, 'order' => 1],
            ['work_order_id' => 12, 'description' => 'Pasang baterai baru 12V 9Ah',                    'is_checked' => false, 'order' => 2],
            ['work_order_id' => 12, 'description' => 'Uji kapasitas & waktu backup UPS',               'is_checked' => false, 'order' => 3],
        ];

        foreach ($checklists as $item) {
            WorkOrderChecklistItem::create($item);
        }
    }
}
