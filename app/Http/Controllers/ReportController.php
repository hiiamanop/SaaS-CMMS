<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $years = range(now()->year - 2, now()->year + 1);

        $data = ReportService::forMonth($year, $month);

        return view('reports.index', array_merge($data, compact('year', 'month', 'years')));
    }

    public function exportPdf(Request $request)
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $data = ReportService::forMonth($year, $month);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reports.pdf.monthly',
            array_merge($data, compact('year', 'month'))
        )->setPaper('a4', 'portrait');

        $monthName = \Carbon\Carbon::create()->month($month)->format('F');
        return $pdf->download("LAPORAN_BULANAN_{$monthName}_{$year}.pdf");
    }
}
