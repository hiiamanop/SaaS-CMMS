<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\ChecksheetSession;
use App\Models\SparePart;
use App\Models\WorkOrder;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAssets = Asset::count();
        $openWorkOrders = WorkOrder::whereIn('status', ['open', 'in_progress'])->count();
        $overdueWorkOrders = WorkOrder::whereNotIn('status', ['closed'])
            ->where('due_date', '<', now())->count();
        $lowStockCount = SparePart::whereRaw('qty_actual <= qty_minimum')->count();
        $pendingChecksheets = ChecksheetSession::where('status', 'draft')
            ->where('year', now()->year)->count();

        $recentWorkOrders = WorkOrder::with(['asset', 'assignedTo'])
            ->latest()->take(8)->get();

        $upcomingSchedules = ChecksheetSession::with(['schedule.location'])
            ->where('status', 'draft')
            ->where('year', now()->year)
            ->where(function($q) {
                // If weekly, filter by current month
                $q->whereNull('month')->orWhere('month', now()->month);
            })
            ->latest()
            ->take(2)->get();

        $lowStockParts = SparePart::whereRaw('qty_actual <= qty_minimum')
            ->orderBy('qty_actual')->take(8)->get();

        // Chart data: work orders last 6 months
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $chartData[] = [
                'label' => $month->format('M Y'),
                'open' => WorkOrder::whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->where('status', 'open')->count(),
                'closed' => WorkOrder::whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->where('status', 'closed')->count(),
            ];
        }

        return view('dashboard', compact(
            'totalAssets', 'openWorkOrders', 'overdueWorkOrders', 'lowStockCount', 'pendingChecksheets',
            'recentWorkOrders', 'upcomingSchedules', 'lowStockParts', 'chartData'
        ));
    }
}
