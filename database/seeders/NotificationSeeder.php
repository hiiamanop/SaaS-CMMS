<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $notifications = [
            // Admin notifications
            ['user_id' => 1, 'type' => 'low_stock', 'title' => 'Low Stock Alert', 'message' => 'Spare part "Circuit Breaker 16A" (SP-010) is out of stock. Current qty: 0, Minimum: 5', 'url' => '/spare-parts/10', 'is_read' => false, 'created_at' => '2026-03-28 08:00:00'],
            ['user_id' => 1, 'type' => 'low_stock', 'title' => 'Low Stock Alert', 'message' => 'Spare part "Fuse 10A Glass" (SP-006) is critically low. Current qty: 1, Minimum: 10', 'url' => '/spare-parts/6', 'is_read' => false, 'created_at' => '2026-03-28 08:01:00'],
            ['user_id' => 1, 'type' => 'overdue_wo', 'title' => 'Overdue Work Order', 'message' => 'Work Order WO-202603-0005 "Air compressor safety valve test" is overdue since 2026-03-25', 'url' => '/work-orders/11', 'is_read' => false, 'created_at' => '2026-03-26 07:00:00'],
            ['user_id' => 1, 'type' => 'status_changed', 'title' => 'Work Order Updated', 'message' => 'Work Order WO-202603-0002 status changed to Pending Review by Alice Technician', 'url' => '/work-orders/8', 'is_read' => true, 'read_at' => '2026-03-13 09:00:00', 'created_at' => '2026-03-12 16:00:00'],
            ['user_id' => 1, 'type' => 'low_stock', 'title' => 'Low Stock Alert', 'message' => 'Spare part "Encoder Incremental 1000PPR" (SP-020) is out of stock. Current qty: 0, Minimum: 1', 'url' => '/spare-parts/20', 'is_read' => true, 'read_at' => '2026-03-20 10:00:00', 'created_at' => '2026-03-15 09:00:00'],
            // Supervisor notifications
            ['user_id' => 2, 'type' => 'low_stock', 'title' => 'Low Stock Alert', 'message' => 'Spare part "Circuit Breaker 16A" (SP-010) is out of stock. Current qty: 0, Minimum: 5', 'url' => '/spare-parts/10', 'is_read' => false, 'created_at' => '2026-03-28 08:00:00'],
            ['user_id' => 2, 'type' => 'overdue_wo', 'title' => 'Overdue Work Order', 'message' => 'Work Order WO-202603-0005 "Air compressor safety valve test" is overdue since 2026-03-25', 'url' => '/work-orders/11', 'is_read' => false, 'created_at' => '2026-03-26 07:00:00'],
            ['user_id' => 2, 'type' => 'overdue_wo', 'title' => 'Overdue Work Order', 'message' => 'Work Order WO-202602-0003 "Water treatment pump seal leak" is overdue since 2026-02-28', 'url' => '/work-orders/6', 'is_read' => false, 'created_at' => '2026-03-01 07:00:00'],
            ['user_id' => 2, 'type' => 'status_changed', 'title' => 'Work Order Updated', 'message' => 'Work Order WO-202603-0002 status changed to Pending Review by Bob Technician', 'url' => '/work-orders/8', 'is_read' => true, 'read_at' => '2026-03-13 09:00:00', 'created_at' => '2026-03-12 16:00:00'],
            // Technician 1 (Alice)
            ['user_id' => 3, 'type' => 'new_wo', 'title' => 'New Work Order Assigned', 'message' => 'You have been assigned Work Order WO-202603-0005 "Air compressor safety valve test"', 'url' => '/work-orders/11', 'is_read' => false, 'created_at' => '2026-03-15 09:00:00'],
            ['user_id' => 3, 'type' => 'new_wo', 'title' => 'New Work Order Assigned', 'message' => 'You have been assigned Work Order WO-202604-0003 "Overhead crane annual inspection"', 'url' => '/work-orders/15', 'is_read' => false, 'created_at' => '2026-03-25 14:00:00'],
            // Technician 2 (Bob)
            ['user_id' => 4, 'type' => 'new_wo', 'title' => 'New Work Order Assigned', 'message' => 'You have been assigned Work Order WO-202603-0006 "Conveyor motor replacement"', 'url' => '/work-orders/12', 'is_read' => true, 'read_at' => '2026-03-20 07:00:00', 'created_at' => '2026-03-19 17:00:00'],
            ['user_id' => 4, 'type' => 'new_wo', 'title' => 'New Work Order Assigned', 'message' => 'You have been assigned Work Order WO-202603-0004 "Robot arm calibration"', 'url' => '/work-orders/10', 'is_read' => false, 'created_at' => '2026-03-28 10:00:00'],
        ];

        foreach ($notifications as $notif) {
            Notification::create($notif);
        }
    }
}
