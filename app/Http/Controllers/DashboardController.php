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
        $overdueWorkOrders = WorkOrder::whereNotIn('status', ['closed', 'canceled', 'solved'])
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

        $pvMapData = Asset::whereNotNull('transformer_block')
            ->whereNotNull('string_number')
            ->whereNotNull('module_slot')
            ->where('category', 'PV Module')
            ->with('locationId:id,name')
            ->select([
                'id', 'name', 'asset_code', 'transformer_block', 'string_number',
                'module_slot', 'visual_row', 'visual_col', 'status', 'location_id',
                'brand', 'model', 'serial_number', 'description'
            ])
            ->orderBy('transformer_block')
            ->orderBy('string_number')
            ->orderBy('module_slot')
            ->get()
            ->groupBy('transformer_block');

        $blockLocations = $pvMapData->map(
            fn($modules) => optional($modules->first()->locationId)->name ?? ''
        )->toArray();

        $blockLocationIds = $pvMapData->map(
            fn($modules) => $modules->first()->location_id ?? null
        )->toArray();

        $supportingAssets = Asset::whereIn('category', ['Inverter', 'Transformer', 'Metering'])
            ->whereNotNull('transformer_block')
            ->select(['id', 'name', 'asset_code', 'category', 'brand', 'model', 'status', 'transformer_block', 'visual_row', 'visual_col'])
            ->orderBy('transformer_block')
            ->orderByRaw("CASE category WHEN 'Inverter' THEN 1 WHEN 'Transformer' THEN 2 WHEN 'Metering' THEN 3 ELSE 4 END")
            ->get()
            ->groupBy('transformer_block');

        $activeWorkOrders = WorkOrder::whereIn('status', ['open', 'in_progress', 'pending_review'])
            ->whereNotNull('asset_id')
            ->select(['id', 'wo_number', 'title', 'asset_id', 'status', 'priority'])
            ->get()
            ->groupBy('asset_id');

        $assetWoMap = $activeWorkOrders->map(function ($wos) {
            $latest = $wos->first();
            return [
                'count' => $wos->count(),
                'latest_wo' => $latest->wo_number,
                'latest_title' => $latest->title,
                'status' => $latest->status,
                'priority' => $latest->priority,
            ];
        })->toArray();

        return view('dashboard', compact(
            'totalAssets', 'openWorkOrders', 'overdueWorkOrders', 'lowStockCount', 'pendingChecksheets',
            'recentWorkOrders', 'upcomingSchedules', 'lowStockParts', 'chartData', 'pvMapData', 'supportingAssets', 'blockLocations', 'blockLocationIds', 'assetWoMap'
        ));
    }
}
