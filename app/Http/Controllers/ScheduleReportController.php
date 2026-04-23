<?php

namespace App\Http\Controllers;

use App\Models\ChecksheetSession;
use App\Models\MaintenanceSchedule;
use App\Models\WorkOrder;
use Illuminate\Http\Request;

class ScheduleReportController extends Controller
{
    public function index(Request $request)
    {
        $year         = $request->get('year', now()->year);
        $pltsLocation = $request->get('plts_location');
        $category     = $request->get('category');

        $years         = range(now()->year - 2, now()->year + 1);
        $pltsLocations = ['PLTS Pertiwi Lestari', 'PLTS Rengiat', 'PLTS Demo Site'];
        $categories    = ['PV Module', 'Inverter', 'Panel LV', 'Transformer'];

        $schedules = MaintenanceSchedule::with('location')
            ->when($category, fn($q) => $q->where('category', $category))
            ->where('status', 'active')
            ->get();

        $workOrders = WorkOrder::with('asset')
            ->whereYear('due_date', $year)
            ->get();

        // Sessions grouped by schedule
        $sessions = ChecksheetSession::with(['schedule', 'results'])
            ->where('year', $year)
            ->when($pltsLocation, fn($q) => $q->where('plts_location', $pltsLocation))
            ->get();

        return view('schedule-report.index', compact(
            'year', 'pltsLocation', 'category', 'years', 'pltsLocations', 'categories',
            'schedules', 'workOrders', 'sessions'
        ));
    }

    public function exportPdf(Request $request, string $tab)
    {
        $year         = $request->get('year', now()->year);
        $pltsLocation = $request->get('plts_location', 'All');
        $category     = $request->get('category');

        $sessions = ChecksheetSession::with(['schedule', 'results.template'])
            ->where('year', $year)
            ->when($pltsLocation && $pltsLocation !== 'All', fn($q) => $q->where('plts_location', $pltsLocation))
            ->get();

        $schedules = MaintenanceSchedule::with('location')
            ->when($category, fn($q) => $q->where('category', $category))
            ->where('status', 'active')
            ->get();

        $workOrders = WorkOrder::with('asset')
            ->whereYear('due_date', $year)
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView("schedule-report.pdf.{$tab}", compact(
            'year', 'pltsLocation', 'sessions', 'schedules', 'workOrders'
        ))->setPaper('a4', 'landscape');

        $tabLabels = [
            'schedule'   => 'PREVENTIVE_MAINTENANCE_SCHEDULE',
            'mingguan'   => 'CHECKSHEET_MINGGUAN',
            'bulanan'    => 'CHECKSHEET_BULANAN',
            'semesteran' => 'CHECKSHEET_SEMESTERAN',
            'tahunan'    => 'CHECKSHEET_TAHUNAN',
        ];
        $filename = ($tabLabels[$tab] ?? 'REPORT') . '_' . str_replace(' ', '-', $pltsLocation) . '_' . $year . '.pdf';

        return $pdf->download($filename);
    }
}
