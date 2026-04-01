<?php

namespace App\Http\Controllers;

use App\Models\Asset;
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

        $recentWorkOrders = WorkOrder::with(['asset', 'assignedTo'])
            ->latest()->take(8)->get();

        $upcomingSchedules = MaintenanceSchedule::with('asset')
            ->where('status', 'active')
            ->where('next_due_date', '>=', now())
            ->where('next_due_date', '<=', now()->addDays(7))
            ->orderBy('next_due_date')
            ->take(5)->get();

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
                'in_progress' => WorkOrder::whereYear('created_at', $month->year)->whereMonth('created_at', $month->month)->where('status', 'in_progress')->count(),
            ];
        }

        return view('dashboard', compact(
            'totalAssets', 'openWorkOrders', 'overdueWorkOrders', 'lowStockCount',
            'recentWorkOrders', 'upcomingSchedules', 'lowStockParts', 'chartData'
        ));
    }
}
