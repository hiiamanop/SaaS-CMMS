<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// CMMS Scheduled Jobs — run daily
Schedule::command('cmms:generate-sessions')->yearlyOn(1, 1, '00:01')->description('Generate checksheet sessions for new year');
Schedule::command('cmms:check-schedules')->dailyAt('06:00')->description('Auto-create WOs for due planned weeks');
Schedule::command('cmms:check-overdue')->dailyAt('07:00')->description('Flag overdue WOs and notify');
Schedule::command('cmms:check-stock')->dailyAt('07:00')->description('Check low spare parts and notify');
Schedule::command('cmms:check-checksheets')->dailyAt('17:00')->description('Remind about unfilled checksheets');
