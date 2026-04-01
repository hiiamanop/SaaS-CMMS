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
            ['wo_number' => 'WO-202601-0001', 'title' => 'Replace oil filter - Air Compressor A', 'asset_id' => 1, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'preventive', 'priority' => 'medium', 'status' => 'closed', 'due_date' => '2026-01-15', 'started_at' => '2026-01-10 08:00:00', 'completed_at' => '2026-01-10 10:30:00', 'description' => 'Monthly PM - replace oil filter and check oil level'],
            ['wo_number' => 'WO-202601-0002', 'title' => 'Conveyor belt misalignment fix', 'asset_id' => 3, 'assigned_to' => 4, 'created_by' => 2, 'type' => 'corrective', 'priority' => 'high', 'status' => 'closed', 'due_date' => '2026-01-20', 'started_at' => '2026-01-18 09:00:00', 'completed_at' => '2026-01-18 14:00:00', 'description' => 'Belt has misaligned, causing production slowdown'],
            ['wo_number' => 'WO-202601-0003', 'title' => 'Generator monthly test run', 'asset_id' => 6, 'assigned_to' => 3, 'created_by' => 1, 'type' => 'preventive', 'priority' => 'low', 'status' => 'closed', 'due_date' => '2026-01-25', 'started_at' => '2026-01-25 07:00:00', 'completed_at' => '2026-01-25 09:00:00', 'description' => 'Monthly generator test run and inspection'],
            ['wo_number' => 'WO-202602-0001', 'title' => 'CNC spindle bearing replacement', 'asset_id' => 2, 'assigned_to' => 4, 'created_by' => 2, 'type' => 'corrective', 'priority' => 'critical', 'status' => 'closed', 'due_date' => '2026-02-05', 'started_at' => '2026-02-03 06:00:00', 'completed_at' => '2026-02-04 18:00:00', 'description' => 'Spindle bearing worn out, machine vibrating'],
            ['wo_number' => 'WO-202602-0002', 'title' => 'Forklift battery maintenance', 'asset_id' => 5, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'preventive', 'priority' => 'medium', 'status' => 'closed', 'due_date' => '2026-02-10', 'started_at' => '2026-02-10 08:00:00', 'completed_at' => '2026-02-10 11:00:00', 'description' => 'Monthly forklift battery check and water top-up'],
            ['wo_number' => 'WO-202602-0003', 'title' => 'Water treatment pump seal leak', 'asset_id' => 7, 'assigned_to' => 4, 'created_by' => 1, 'type' => 'corrective', 'priority' => 'high', 'status' => 'in_progress', 'due_date' => '2026-02-28', 'started_at' => '2026-02-20 09:00:00', 'description' => 'Pump seal leaking, needs immediate replacement'],
            ['wo_number' => 'WO-202603-0001', 'title' => 'Overhead crane wire rope inspection', 'asset_id' => 8, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'preventive', 'priority' => 'high', 'status' => 'closed', 'due_date' => '2026-03-05', 'started_at' => '2026-03-05 07:00:00', 'completed_at' => '2026-03-05 10:00:00', 'description' => 'Weekly crane inspection including wire rope'],
            ['wo_number' => 'WO-202603-0002', 'title' => 'Industrial chiller refrigerant top-up', 'asset_id' => 4, 'assigned_to' => 4, 'created_by' => 2, 'type' => 'corrective', 'priority' => 'high', 'status' => 'pending_review', 'due_date' => '2026-03-15', 'started_at' => '2026-03-12 08:00:00', 'completed_at' => '2026-03-12 16:00:00', 'description' => 'Chiller losing cooling efficiency, refrigerant top-up needed'],
            ['wo_number' => 'WO-202603-0003', 'title' => 'Hydraulic press oil change', 'asset_id' => 9, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'preventive', 'priority' => 'medium', 'status' => 'open', 'due_date' => '2026-04-10', 'description' => 'Quarterly hydraulic oil change scheduled'],
            ['wo_number' => 'WO-202603-0004', 'title' => 'Robot arm calibration and maintenance', 'asset_id' => 10, 'assigned_to' => 4, 'created_by' => 1, 'type' => 'preventive', 'priority' => 'medium', 'status' => 'open', 'due_date' => '2026-04-15', 'description' => 'Annual robot arm calibration'],
            ['wo_number' => 'WO-202603-0005', 'title' => 'Air compressor safety valve test', 'asset_id' => 1, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'preventive', 'priority' => 'high', 'status' => 'open', 'due_date' => '2026-03-25', 'description' => 'Test and certify safety valve operation'],
            ['wo_number' => 'WO-202603-0006', 'title' => 'Conveyor motor replacement', 'asset_id' => 3, 'assigned_to' => 4, 'created_by' => 2, 'type' => 'corrective', 'priority' => 'critical', 'status' => 'in_progress', 'due_date' => '2026-03-22', 'started_at' => '2026-03-20 06:00:00', 'description' => 'Drive motor burnt out, production stopped'],
            ['wo_number' => 'WO-202604-0001', 'title' => 'Generator quarterly service', 'asset_id' => 6, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'preventive', 'priority' => 'medium', 'status' => 'open', 'due_date' => '2026-04-20', 'description' => 'Quarterly generator service and load test'],
            ['wo_number' => 'WO-202604-0002', 'title' => 'CNC machine coolant system flush', 'asset_id' => 2, 'assigned_to' => 4, 'created_by' => 2, 'type' => 'preventive', 'priority' => 'low', 'status' => 'open', 'due_date' => '2026-04-25', 'description' => 'Flush and replace CNC coolant'],
            ['wo_number' => 'WO-202604-0003', 'title' => 'Overhead crane annual inspection', 'asset_id' => 8, 'assigned_to' => 3, 'created_by' => 1, 'type' => 'preventive', 'priority' => 'high', 'status' => 'open', 'due_date' => '2026-04-30', 'description' => 'Annual statutory crane inspection'],
            ['wo_number' => 'WO-202601-0004', 'title' => 'Hydraulic hose leak repair', 'asset_id' => 9, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'corrective', 'priority' => 'high', 'status' => 'closed', 'due_date' => '2026-01-12', 'started_at' => '2026-01-11 08:00:00', 'completed_at' => '2026-01-11 13:00:00', 'description' => 'Hydraulic hose burst on press #2'],
            ['wo_number' => 'WO-202601-0005', 'title' => 'Forklift tire replacement', 'asset_id' => 5, 'assigned_to' => 4, 'created_by' => 2, 'type' => 'corrective', 'priority' => 'medium', 'status' => 'closed', 'due_date' => '2026-01-28', 'started_at' => '2026-01-27 09:00:00', 'completed_at' => '2026-01-27 12:00:00', 'description' => 'Front tires worn beyond safe limit'],
            ['wo_number' => 'WO-202602-0004', 'title' => 'Chiller condenser coil cleaning', 'asset_id' => 4, 'assigned_to' => 3, 'created_by' => 2, 'type' => 'preventive', 'priority' => 'medium', 'status' => 'closed', 'due_date' => '2026-02-20', 'started_at' => '2026-02-18 08:00:00', 'completed_at' => '2026-02-18 15:00:00', 'description' => 'Quarterly condenser cleaning'],
            ['wo_number' => 'WO-202602-0005', 'title' => 'PLC battery replacement', 'asset_id' => 3, 'assigned_to' => 4, 'created_by' => 1, 'type' => 'preventive', 'priority' => 'low', 'status' => 'closed', 'due_date' => '2026-02-25', 'started_at' => '2026-02-24 10:00:00', 'completed_at' => '2026-02-24 11:00:00', 'description' => 'Annual PLC backup battery replacement'],
            ['wo_number' => 'WO-202603-0007', 'title' => 'Bearing replacement - compressor', 'asset_id' => 1, 'assigned_to' => null, 'created_by' => 2, 'type' => 'corrective', 'priority' => 'high', 'status' => 'open', 'due_date' => '2026-03-20', 'description' => 'Unusual noise from compressor, bearing suspected'],
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
