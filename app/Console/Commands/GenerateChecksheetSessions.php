<?php

namespace App\Console\Commands;

use App\Models\MaintenanceSchedule;
use Illuminate\Console\Command;

class GenerateChecksheetSessions extends Command
{
    protected $signature   = 'cmms:generate-sessions {--year= : Tahun target (default: tahun berjalan)}';
    protected $description = 'Auto-generate checksheet sessions untuk semua jadwal aktif berdasarkan planned_weeks';

    public function handle(): void
    {
        $year = (int) ($this->option('year') ?? now()->year);

        $this->info("Generating checksheet sessions for year {$year}...");

        $schedules = MaintenanceSchedule::with('asset')
            ->where('status', 'active')
            ->get();

        $total = 0;
        foreach ($schedules as $schedule) {
            $count = $schedule->generateYearSessions($year);
            if ($count > 0) {
                $this->line("  [{$schedule->equipment_name}] +{$count} sessions");
                $total += $count;
            }
        }

        $this->info("Done. Total {$total} new sessions created for {$schedules->count()} schedules.");
    }
}
