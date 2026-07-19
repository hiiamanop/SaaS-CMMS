<?php

namespace Tests\Feature;

use App\Models\MonthlyReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateMonthlyReportCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testCommandGeneratesAndArchivesReport()
    {
        Storage::fake();

        $this->artisan('cmms:generate-monthly-report', ['--year' => 2026, '--month' => 7])
            ->assertExitCode(0);

        $report = MonthlyReport::where('year', 2026)->where('month', 7)->first();
        $this->assertNotNull($report);
        Storage::assertExists($report->pdf_path);
    }

    /** @test */
    public function testCommandIsIdempotentPerMonth()
    {
        Storage::fake();

        $this->artisan('cmms:generate-monthly-report', ['--year' => 2026, '--month' => 7]);
        $this->artisan('cmms:generate-monthly-report', ['--year' => 2026, '--month' => 7]);

        $this->assertEquals(1, MonthlyReport::where('year', 2026)->where('month', 7)->count());
    }
}
