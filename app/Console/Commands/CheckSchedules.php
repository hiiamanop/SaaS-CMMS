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
                        ->whereYear('due_date', $now->year)
                        ->whereMonth('due_date', $currentMonth)
                        ->exists();

                    if (!$exists) {
                        $admin = User::where('role', 'admin')->first();

                        $itemsList = is_array($schedule->item_pekerjaan)
                            ? implode(', ', array_filter(array_map(fn($i) => is_array($i) ? ($i['name'] ?? '') : (string)$i, $schedule->item_pekerjaan)))
                            : (string) $schedule->item_pekerjaan;

                        $woType = match($schedule->frequency) {
                            'weekly'    => 'preventive_mingguan',
                            'monthly'   => 'preventive_bulanan',
                            'quarterly' => 'preventive_semesteran',
                            'annually'  => 'preventive_tahunan',
                            default     => 'preventive',
                        };

                        $title = 'Preventive - ' . $schedule->equipment_name . ($itemsList ? ' - ' . \Illuminate\Support\Str::limit($itemsList, 50) : '');

                        WorkOrder::create([
                            'wo_number'               => WorkOrder::generateNumber(),
                            'title'                   => $title,
                            'asset_id'                => $schedule->asset_id,
                            'maintenance_schedule_id' => $schedule->id,
                            'created_by'              => $admin?->id ?? 1,
                            'type'                    => $woType,
                            'priority'                => 'medium',
                            'status'                  => 'open',
                            'due_date'                => $now->endOfWeek()->toDateString(),
                            'description'             => $itemsList ?: 'Preventive maintenance based on schedule.',
                        ]);
                        $this->info("Created WO for schedule #{$schedule->id}: {$schedule->equipment_name}");
                    }
                }
            }
        }

        $this->info('Check schedules complete.');
    }
}
