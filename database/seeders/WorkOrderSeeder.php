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
        $orders = [
            ['wo_number' => 'WO-202601-0001', 'title' => 'Pengecekan Bulanan Inverter 01', 'asset_id' => 1, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'preventive_bulanan', 'priority' => 'medium', 'status' => 'closed', 'due_date' => '2026-01-15', 'start_date' => '2026-01-10', 'started_at' => '2026-01-10 08:00:00', 'completed_at' => '2026-01-10 10:30:00', 'description' => 'Pengecekan bulanan inverter 01 sesuai jadwal PM'],
            ['wo_number' => 'WO-202601-0002', 'title' => 'Perbaikan Kabel DC String Putus', 'asset_id' => 2, 'assigned_to' => 4, 'created_by' => 2, 'type' => 'corrective', 'priority' => 'high', 'status' => 'closed', 'due_date' => '2026-01-20', 'start_date' => '2026-01-18', 'started_at' => '2026-01-18 09:00:00', 'completed_at' => '2026-01-18 14:00:00', 'description' => 'Kabel DC string putus menyebabkan output menurun'],
            ['wo_number' => 'WO-202601-0003', 'title' => 'Pengecekan Mingguan Panel LV', 'asset_id' => 3, 'assigned_to' => 3, 'created_by' => 1, 'type' => 'preventive_mingguan', 'priority' => 'low', 'status' => 'closed', 'due_date' => '2026-01-25', 'start_date' => '2026-01-25', 'started_at' => '2026-01-25 07:00:00', 'completed_at' => '2026-01-25 09:00:00', 'description' => 'Pengecekan mingguan panel LV sesuai checksheet'],
            ['wo_number' => 'WO-202602-0001', 'title' => 'Penggantian Grounding Inverter 02', 'asset_id' => 4, 'assigned_to' => 4, 'created_by' => 2, 'type' => 'corrective', 'priority' => 'critical', 'status' => 'closed', 'due_date' => '2026-02-05', 'start_date' => '2026-02-03', 'started_at' => '2026-02-03 06:00:00', 'completed_at' => '2026-02-04 18:00:00', 'description' => 'Grounding inverter 02 aus, perlu penggantian segera'],
            ['wo_number' => 'WO-202602-0002', 'title' => 'Pengecekan Bulanan PV Module', 'asset_id' => 5, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'preventive_bulanan', 'priority' => 'medium', 'status' => 'closed', 'due_date' => '2026-02-10', 'start_date' => '2026-02-10', 'started_at' => '2026-02-10 08:00:00', 'completed_at' => '2026-02-10 11:00:00', 'description' => 'Pengecekan kondisi PV Module bulanan'],
            ['wo_number' => 'WO-202602-0003', 'title' => 'Kebocoran Oil Transformer 01', 'asset_id' => 6, 'assigned_to' => 4, 'created_by' => 1, 'type' => 'corrective', 'priority' => 'high', 'status' => 'in_progress', 'due_date' => '2026-02-28', 'start_date' => '2026-02-20', 'started_at' => '2026-02-20 09:00:00', 'description' => 'Ditemukan kebocoran oli pada transformer 01'],
            ['wo_number' => 'WO-202603-0001', 'title' => 'Pengecekan Semesteran Inverter', 'asset_id' => 7, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'preventive_semesteran', 'priority' => 'high', 'status' => 'closed', 'due_date' => '2026-03-05', 'start_date' => '2026-03-05', 'started_at' => '2026-03-05 07:00:00', 'completed_at' => '2026-03-05 10:00:00', 'description' => 'Pengecekan semesteran inverter sesuai jadwal'],
            ['wo_number' => 'WO-202603-0002', 'title' => 'Perbaikan MC4 Connector Terbakar', 'asset_id' => 8, 'assigned_to' => 4, 'created_by' => 2, 'type' => 'corrective', 'priority' => 'high', 'status' => 'pending_review', 'due_date' => '2026-03-15', 'start_date' => '2026-03-12', 'started_at' => '2026-03-12 08:00:00', 'completed_at' => '2026-03-12 16:00:00', 'description' => 'MC4 connector terbakar akibat overheating'],
            ['wo_number' => 'WO-202603-0003', 'title' => 'Pengecekan Mingguan Inverter 03', 'asset_id' => 9, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'preventive_mingguan', 'priority' => 'medium', 'status' => 'open', 'due_date' => '2026-04-10', 'description' => 'Pengecekan mingguan inverter 03'],
            ['wo_number' => 'WO-202603-0004', 'title' => 'Overhaul Tahunan Transformer 02', 'asset_id' => 10, 'assigned_to' => 4, 'created_by' => 1, 'type' => 'preventive_tahunan', 'priority' => 'medium', 'status' => 'open', 'due_date' => '2026-04-15', 'description' => 'Overhaul tahunan termasuk BDV test dan DGA test'],
            ['wo_number' => 'WO-202603-0005', 'title' => 'SPD Panel LV Rusak', 'asset_id' => 1, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'corrective', 'priority' => 'high', 'status' => 'open', 'due_date' => '2026-03-25', 'description' => 'SPD panel LV ditemukan rusak saat inspeksi'],
            ['wo_number' => 'WO-202603-0006', 'title' => 'Penggantian Busbar Panel LV', 'asset_id' => 3, 'assigned_to' => 4, 'created_by' => 2, 'type' => 'corrective', 'priority' => 'critical', 'status' => 'in_progress', 'due_date' => '2026-03-22', 'start_date' => '2026-03-20', 'started_at' => '2026-03-20 06:00:00', 'description' => 'Busbar panel LV gosong akibat loose connection'],
            ['wo_number' => 'WO-202604-0001', 'title' => 'Pengecekan Bulanan Transformer 01', 'asset_id' => 6, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'preventive_bulanan', 'priority' => 'medium', 'status' => 'open', 'due_date' => '2026-04-20', 'description' => 'Pengecekan kondisi bulanan transformer 01'],
            ['wo_number' => 'WO-202604-0002', 'title' => 'Pengecekan Thermal MCCB', 'asset_id' => 2, 'assigned_to' => 4, 'created_by' => 2, 'type' => 'preventive_bulanan', 'priority' => 'low', 'status' => 'open', 'due_date' => '2026-04-25', 'description' => 'Thermal monitoring MCCB di peak hour'],
            ['wo_number' => 'WO-202604-0003', 'title' => 'Emergency: Inverter 01 Trip', 'asset_id' => 8, 'assigned_to' => 3, 'created_by' => 1, 'type' => 'emergency', 'priority' => 'critical', 'status' => 'open', 'due_date' => '2026-04-30', 'description' => 'Inverter 01 trip mendadak, perlu pengecekan segera'],
            ['wo_number' => 'WO-202601-0004', 'title' => 'Penggantian Kabel Grounding PV', 'asset_id' => 9, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'corrective', 'priority' => 'high', 'status' => 'closed', 'due_date' => '2026-01-12', 'start_date' => '2026-01-11', 'started_at' => '2026-01-11 08:00:00', 'completed_at' => '2026-01-11 13:00:00', 'description' => 'Kabel grounding PV module terlepas ditemukan saat inspeksi'],
            ['wo_number' => 'WO-202601-0005', 'title' => 'Pembersihan Panel LV Bulanan', 'asset_id' => 5, 'assigned_to' => 4, 'created_by' => 2, 'type' => 'preventive_bulanan', 'priority' => 'medium', 'status' => 'closed', 'due_date' => '2026-01-28', 'start_date' => '2026-01-27', 'started_at' => '2026-01-27 09:00:00', 'completed_at' => '2026-01-27 12:00:00', 'description' => 'Pembersihan dan pengecekan kondisi panel LV'],
            ['wo_number' => 'WO-202602-0004', 'title' => 'Pengecekan Torque Bolt Panel LV', 'asset_id' => 4, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'preventive_semesteran', 'priority' => 'medium', 'status' => 'closed', 'due_date' => '2026-02-20', 'start_date' => '2026-02-18', 'started_at' => '2026-02-18 08:00:00', 'completed_at' => '2026-02-18 15:00:00', 'description' => 'Pemeriksaan torque semua baut panel LV'],
            ['wo_number' => 'WO-202602-0005', 'title' => 'Penggantian Fan Inverter 02', 'asset_id' => 3, 'assigned_to' => 4, 'created_by' => 1, 'type' => 'corrective', 'priority' => 'low', 'status' => 'closed', 'due_date' => '2026-02-25', 'start_date' => '2026-02-24', 'started_at' => '2026-02-24 10:00:00', 'completed_at' => '2026-02-24 11:00:00', 'description' => 'Fan inverter 02 berisik, perlu penggantian'],
            ['wo_number' => 'WO-202603-0007', 'title' => 'Pengecekan Mingguan Inverter 01', 'asset_id' => 1, 'assigned_to' => null, 'assigned_to_external' => 'PT. Mitra Energi Surya', 'created_by' => 2, 'type' => 'preventive_mingguan', 'priority' => 'high', 'status' => 'open', 'due_date' => '2026-03-20', 'description' => 'Pengecekan mingguan inverter 01 dilakukan oleh pihak eksternal'],
        ];

        foreach ($orders as $order) {
            WorkOrder::create($order);
        }

        // Add activity logs for closed/in-progress work orders
        $logs = [
            ['work_order_id' => 1, 'user_id' => 3, 'from_status' => 'open', 'to_status' => 'in_progress', 'notes' => 'Starting work on oil filter replacement', 'created_at' => '2026-01-10 08:00:00'],
            ['work_order_id' => 1, 'user_id' => 3, 'from_status' => 'in_progress', 'to_status' => 'closed', 'notes' => 'Oil filter replaced, oil level topped up', 'created_at' => '2026-01-10 10:30:00'],
            ['work_order_id' => 4, 'user_id' => 4, 'from_status' => 'open', 'to_status' => 'in_progress', 'notes' => 'Started bearing disassembly', 'created_at' => '2026-02-03 06:00:00'],
            ['work_order_id' => 4, 'user_id' => 4, 'from_status' => 'in_progress', 'to_status' => 'closed', 'notes' => 'New bearing installed, machine running smooth', 'created_at' => '2026-02-04 18:00:00'],
            ['work_order_id' => 6, 'user_id' => 4, 'from_status' => 'open', 'to_status' => 'in_progress', 'notes' => 'Ordered replacement seal kit, starting disassembly', 'created_at' => '2026-02-20 09:00:00'],
            ['work_order_id' => 8, 'user_id' => 4, 'from_status' => 'open', 'to_status' => 'in_progress', 'notes' => 'Refrigerant top-up in progress', 'created_at' => '2026-03-12 08:00:00'],
            ['work_order_id' => 8, 'user_id' => 4, 'from_status' => 'in_progress', 'to_status' => 'pending_review', 'notes' => 'Refrigerant topped up, chiller running at normal efficiency now', 'created_at' => '2026-03-12 16:00:00'],
            ['work_order_id' => 12, 'user_id' => 4, 'from_status' => 'open', 'to_status' => 'in_progress', 'notes' => 'Motor ordered and received, starting replacement', 'created_at' => '2026-03-20 06:00:00'],
        ];

        foreach ($logs as $log) {
            WorkOrderActivityLog::create($log);
        }

        // Add checklist items for some work orders
        $checklists = [
            ['work_order_id' => 9, 'description' => 'Drain old hydraulic oil', 'is_checked' => false, 'order' => 1],
            ['work_order_id' => 9, 'description' => 'Clean reservoir and filter housing', 'is_checked' => false, 'order' => 2],
            ['work_order_id' => 9, 'description' => 'Replace hydraulic filter', 'is_checked' => false, 'order' => 3],
            ['work_order_id' => 9, 'description' => 'Fill with new VG46 oil', 'is_checked' => false, 'order' => 4],
            ['work_order_id' => 9, 'description' => 'Test system pressure', 'is_checked' => false, 'order' => 5],
            ['work_order_id' => 11, 'description' => 'Shut down compressor safely', 'is_checked' => false, 'order' => 1],
            ['work_order_id' => 11, 'description' => 'Depressurize system', 'is_checked' => false, 'order' => 2],
            ['work_order_id' => 11, 'description' => 'Test safety valve operation', 'is_checked' => false, 'order' => 3],
            ['work_order_id' => 11, 'description' => 'Document test results', 'is_checked' => false, 'order' => 4],
            ['work_order_id' => 12, 'description' => 'Lock out / tag out conveyor', 'is_checked' => true, 'checked_by' => 4, 'checked_at' => '2026-03-20 06:30:00', 'order' => 1],
            ['work_order_id' => 12, 'description' => 'Remove old motor', 'is_checked' => true, 'checked_by' => 4, 'checked_at' => '2026-03-20 09:00:00', 'order' => 2],
            ['work_order_id' => 12, 'description' => 'Install new motor', 'is_checked' => false, 'order' => 3],
            ['work_order_id' => 12, 'description' => 'Align motor to conveyor shaft', 'is_checked' => false, 'order' => 4],
            ['work_order_id' => 12, 'description' => 'Test run conveyor', 'is_checked' => false, 'order' => 5],
        ];

        foreach ($checklists as $item) {
            WorkOrderChecklistItem::create($item);
        }
    }
}
