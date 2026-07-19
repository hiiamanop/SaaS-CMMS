<?php

namespace App\Console\Commands;

use App\Models\MonthlyReport;
use App\Services\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateMonthlyReport extends Command
{
    protected $signature = 'cmms:generate-monthly-report {--year=} {--month=}';
    protected $description = 'Generate and archive the monthly activity & item-usage report PDF';

    public function handle(): int
    {
        $target = now()->subMonthNoOverflow()->startOfMonth();
        $year  = (int) ($this->option('year') ?: $target->year);
        $month = (int) ($this->option('month') ?: $target->month);

        $data = ReportService::forMonth($year, $month);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reports.pdf.monthly',
            array_merge($data, compact('year', 'month'))
        )->setPaper('a4', 'portrait');

        $path = 'monthly-reports/LAPORAN_BULANAN_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf';
        Storage::put($path, $pdf->output());

        MonthlyReport::updateOrCreate(
            ['year' => $year, 'month' => $month, 'location_id' => null],
            ['pdf_path' => $path, 'generated_at' => now(), 'generated_by_user_id' => null],
        );

        $this->info("Generated monthly report for {$year}-{$month}: {$path}");

        return self::SUCCESS;
    }
}
