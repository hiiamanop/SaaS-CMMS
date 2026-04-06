<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckOverdue extends Command
{
    protected $signature = 'cmms:check-overdue';
    protected $description = 'Flag overdue work orders and send notifications';

    public function handle(): void
    {
        $overdueWOs = WorkOrder::whereNotIn('status', ['closed'])
            ->where('due_date', '<', Carbon::today())
            ->get();

        $adminsAndSpvs = User::whereIn('role', ['admin', 'supervisor'])->get();

        foreach ($overdueWOs as $wo) {
            foreach ($adminsAndSpvs as $user) {
                $alreadyNotified = Notification::where('user_id', $user->id)
                    ->where('type', 'work_order_overdue')
                    ->whereDate('created_at', Carbon::today())
                    ->where('data->work_order_id', $wo->id)
                    ->exists();

                if (!$alreadyNotified) {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'work_order_overdue',
                        'title' => 'Work Order Overdue',
                        'message' => "Work order {$wo->wo_number} - {$wo->title} sudah melewati batas waktu.",
                        'data' => ['work_order_id' => $wo->id],
                        'is_read' => false,
                    ]);
                }
            }
        }

        $this->info("Checked {$overdueWOs->count()} overdue work orders.");
    }
}
