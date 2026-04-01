<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;
use Carbon\Carbon;

class KpiController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : now()->subMonths(6)->startOfMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : now()->endOfMonth();

        $records = MaintenanceRecord::whereBetween('maintenance_date', [$dateFrom, $dateTo])->get();
        $workOrders = WorkOrder::whereBetween('created_at', [$dateFrom, $dateTo])->get();

        $correctiveRecords = $records->where('type', 'corrective');
        $mttr = $correctiveRecords->count() > 0
            ? round($correctiveRecords->avg('duration_minutes') / 60, 2)
            : 0;

        $closedCorrective = $workOrders->where('type', 'corrective')->where('status', 'closed')->sortBy('completed_at');
        $mtbf = 0;
        if ($closedCorrective->count() > 1) {
            $diffs = [];
            $prev = null;
            foreach ($closedCorrective as $wo) {
                if ($prev && $prev->completed_at && ($wo->started_at ?? $wo->created_at)) {
                    $diffs[] = $prev->completed_at->diffInHours($wo->started_at ?? $wo->created_at);
                }
                $prev = $wo;
            }
            $mtbf = count($diffs) > 0 ? round(array_sum($diffs) / count($diffs), 2) : 0;
        }

        $pmScheduled = MaintenanceSchedule::where('status', 'active')
            ->where('next_due_date', '<=', $dateTo)->count();
        $pmDone = $workOrders->where('type', 'preventive')->where('status', 'closed')->count();
        $pmCompliance = $pmScheduled > 0 ? round(($pmDone / $pmScheduled) * 100, 1) : 0;

        $totalWo = $workOrders->count();
        $closedWo = $workOrders->where('status', 'closed')->count();
        $completionRate = $totalWo > 0 ? round(($closedWo / $totalWo) * 100, 1) : 0;

        $overdueWo = WorkOrder::whereNotIn('status', ['closed'])->where('due_date', '<', now())->count();
        $totalDowntime = round($records->sum('downtime_minutes') / 60, 2);

        $monthlyData = [];
        $current = $dateFrom->copy()->startOfMonth();
        while ($current <= $dateTo) {
            $monthWos = $workOrders->filter(fn($wo) => $wo->created_at->format('Y-m') === $current->format('Y-m'));
            $monthlyData[] = [
                'label' => $current->format('M Y'),
                'open' => $monthWos->where('status', 'open')->count(),
                'in_progress' => $monthWos->where('status', 'in_progress')->count(),
                'closed' => $monthWos->where('status', 'closed')->count(),
                'pending_review' => $monthWos->where('status', 'pending_review')->count(),
            ];
            $current->addMonth();
        }

        $mttrTrend = [];
        $current = $dateFrom->copy()->startOfMonth();
        while ($current <= $dateTo) {
            $monthRecords = $records->filter(fn($r) => $r->maintenance_date->format('Y-m') === $current->format('Y-m') && $r->type === 'corrective');
            $mttrTrend[] = [
                'label' => $current->format('M Y'),
                'value' => $monthRecords->count() > 0 ? round($monthRecords->avg('duration_minutes') / 60, 2) : 0,
            ];
            $current->addMonth();
        }

        $byPriority = [
            'low' => $workOrders->where('priority', 'low')->count(),
            'medium' => $workOrders->where('priority', 'medium')->count(),
            'high' => $workOrders->where('priority', 'high')->count(),
            'critical' => $workOrders->where('priority', 'critical')->count(),
        ];

        $downtimeTrend = [];
        $current = $dateFrom->copy()->startOfMonth();
        while ($current <= $dateTo) {
            $monthRecords = $records->filter(fn($r) => $r->maintenance_date->format('Y-m') === $current->format('Y-m'));
            $downtimeTrend[] = [
                'label' => $current->format('M Y'),
                'value' => round($monthRecords->sum('downtime_minutes') / 60, 2),
            ];
            $current->addMonth();
        }

        return view('kpi.index', compact(
            'mttr', 'mtbf', 'pmCompliance', 'completionRate', 'overdueWo', 'totalDowntime',
            'monthlyData', 'mttrTrend', 'byPriority', 'downtimeTrend', 'dateFrom', 'dateTo'
        ));
    }
}
