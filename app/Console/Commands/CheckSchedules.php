<?php

namespace App\Console\Commands;

use App\Models\MaintenanceSchedule;
use App\Models\WorkOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckSchedules extends Command
{
    protected $signature = 'cmms:check-schedules';
    protected $description = 'Auto-create work orders for due planned weeks';

    public function handle(): void
    {
        $now = Carbon::now();
        $currentWeek = $now->weekOfMonth;
        $currentMonth = $now->month;

        $schedules = MaintenanceSchedule::where('status', 'active')->get();

        foreach ($schedules as $schedule) {
            $plannedWeeks = $schedule->planned_weeks ?? [];

            foreach ($plannedWeeks as $planned) {
                if (($planned['month'] ?? null) == $currentMonth && ($planned['week'] ?? null) == $currentWeek) {
                    // Check if work order already exists for this week
                    $exists = WorkOrder::where('maintenance_schedule_id', $schedule->id)
                        ->where('type', $schedule->type === 'mingguan' ? 'preventive_mingguan' : 'preventive_' . $schedule->type)
                        ->whereYear('due_date', $now->year)
                        ->whereMonth('due_date', $currentMonth)
                        ->exists();

                    if (!$exists) {
                        $admin = User::where('role', 'admin')->first();
                        WorkOrder::create([
                            'wo_number' => WorkOrder::generateNumber(),
                            'title' => "Preventive - {$schedule->equipment_name} - {$schedule->item_pekerjaan}",
                            'asset_id' => $schedule->asset_id,
                            'maintenance_schedule_id' => $schedule->id,
                            'created_by' => $admin?->id ?? 1,
                            'type' => 'preventive_' . $schedule->type,
                            'priority' => 'medium',
                            'status' => 'open',
                            'due_date' => $now->endOfWeek()->toDateString(),
                            'description' => $schedule->item_pekerjaan,
                        ]);
                        $this->info("Created WO for schedule #{$schedule->id}: {$schedule->equipment_name}");
                    }
                }
            }
        }

        $this->info('Check schedules complete.');
    }
}
