<?php

namespace App\Console\Commands;

use App\Models\ChecksheetSession;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckChecksheets extends Command
{
    protected $signature = 'cmms:check-checksheets';
    protected $description = 'Check unfilled checksheets at end of day and notify Supervisor & Admin';

    public function handle(): void
    {
        $today = Carbon::today();
        $currentWeek = $today->weekOfMonth;
        $currentMonth = $today->month;
        $currentYear = $today->year;

        // Find draft weekly checksheets that should have been submitted today
        $drafts = ChecksheetSession::with('schedule')->where('status', 'draft')
            ->where('year', $currentYear)
            ->whereNotNull('week_number')
            ->where('week_number', $currentWeek)
            ->where('month', $currentMonth)
            ->get();

        if ($drafts->isEmpty()) {
            $this->info('No unfilled checksheets found.');
            return;
        }

        $adminsAndSpvs = User::whereIn('role', ['admin', 'supervisor'])->get();

        foreach ($drafts as $session) {
            foreach ($adminsAndSpvs as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'checksheet_reminder',
                    'title' => 'Checksheet Belum Disubmit',
                    'message' => "Checksheet {$session->schedule->equipment_name} periode {$session->period_label} belum disubmit.",
                    'data' => ['session_id' => $session->id],
                    'is_read' => false,
                ]);
            }
        }

        $this->info("Sent reminders for {$drafts->count()} unfilled checksheets.");
    }
}
