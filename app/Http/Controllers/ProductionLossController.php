<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\ProductionLoss;
use App\Models\ProductionSector;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductionLossController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $query = ProductionLoss::with(['location', 'sector', 'workOrder', 'creator'])
            ->whereYear('started_at', $year)
            ->whereMonth('started_at', $month)
            ->orderBy('started_at', 'desc');

        if (!$user->isAdmin()) {
            $query->where('location_id', $user->location_id);
        }

        $losses = $query->get();

        $locations = $user->isAdmin()
            ? Location::orderBy('name')->get()
            : Location::where('id', $user->location_id)->get();

        // Monthly summary
        $totalLopKwh     = $losses->sum('lop_kwh');
        $totalDuration   = $losses->sum('duration_minutes');
        $totalEvents     = $losses->count();
        $byCategory      = $losses->groupBy('category')->map->count();

        return view('production-losses.index', compact(
            'losses', 'locations', 'month', 'year',
            'totalLopKwh', 'totalDuration', 'totalEvents', 'byCategory'
        ));
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        $locations = $user->isAdmin()
            ? Location::orderBy('name')->get()
            : Location::where('id', $user->location_id)->get();

        $locationId = $request->get('location_id', $user->location_id ?? $locations->first()?->id);

        $sectors = ProductionSector::where('location_id', $locationId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $openWorkOrders = WorkOrder::where('status', '!=', 'closed')
            ->where('status', '!=', 'canceled')
            ->when(!$user->isAdmin(), fn($q) => $q->whereHas('asset', fn($a) => $a->where('location_id', $user->location_id)))
            ->orderBy('wo_number', 'desc')
            ->limit(50)
            ->get(['id', 'wo_number', 'title']);

        return view('production-losses.create', compact('locations', 'locationId', 'sectors', 'openWorkOrders'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'location_id'          => 'required|exists:locations,id',
            'sector_id'            => 'nullable|exists:production_sectors,id',
            'work_order_id'        => 'nullable|exists:work_orders,id',
            'started_at'           => 'required|date',
            'ended_at'             => 'nullable|date|after:started_at',
            'category'             => 'required|in:' . implode(',', array_keys(ProductionLoss::$categories)),
            'description'          => 'nullable|string|max:2000',
            'trafo'                => 'nullable|string|max:100',
            'inverter'             => 'nullable|string|max:100',
            'string_info'          => 'nullable|string|max:200',
            'affected_strings'     => 'nullable|integer|min:0',
            'affected_capacity_kw' => 'nullable|numeric|min:0',
            'lop_kwh'              => 'nullable|numeric|min:0',
        ]);

        if (!$user->isAdmin() && $validated['location_id'] != $user->location_id) {
            abort(403);
        }

        // Compute duration
        $started    = Carbon::parse($validated['started_at']);
        $ended      = !empty($validated['ended_at']) ? Carbon::parse($validated['ended_at']) : null;
        $durationMin = $ended ? (int) $started->diffInMinutes($ended) : 0;

        // Auto-compute LOP kWh if not provided
        $lopKwh = $validated['lop_kwh'] ?? null;
        if (!$lopKwh && !empty($validated['affected_capacity_kw']) && $durationMin > 0) {
            $lopKwh = round($validated['affected_capacity_kw'] * ($durationMin / 60), 2);
        }

        ProductionLoss::create([
            'location_id'          => $validated['location_id'],
            'sector_id'            => $validated['sector_id'] ?? null,
            'work_order_id'        => $validated['work_order_id'] ?? null,
            'created_by'           => $user->id,
            'started_at'           => $started,
            'ended_at'             => $ended,
            'duration_minutes'     => $durationMin,
            'trafo'                => $validated['trafo'] ?? null,
            'inverter'             => $validated['inverter'] ?? null,
            'string_info'          => $validated['string_info'] ?? null,
            'affected_strings'     => $validated['affected_strings'] ?? null,
            'category'             => $validated['category'],
            'description'          => $validated['description'] ?? null,
            'affected_capacity_kw' => $validated['affected_capacity_kw'] ?? null,
            'lop_kwh'              => $lopKwh,
        ]);

        return redirect()->route('production-losses.index')
            ->with('success', 'Data Loss of Production berhasil disimpan.');
    }

    public function edit(ProductionLoss $productionLoss)
    {
        $user = auth()->user();

        if (!$user->isAdmin() && $productionLoss->location_id != $user->location_id) {
            abort(403);
        }

        $locations = $user->isAdmin()
            ? Location::orderBy('name')->get()
            : Location::where('id', $user->location_id)->get();

        $sectors = ProductionSector::where('location_id', $productionLoss->location_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $openWorkOrders = WorkOrder::orderBy('wo_number', 'desc')
            ->limit(50)
            ->get(['id', 'wo_number', 'title']);

        return view('production-losses.edit', compact('productionLoss', 'locations', 'sectors', 'openWorkOrders'));
    }

    public function update(Request $request, ProductionLoss $productionLoss)
    {
        $user = auth()->user();

        if (!$user->isAdmin() && $productionLoss->location_id != $user->location_id) {
            abort(403);
        }

        $validated = $request->validate([
            'sector_id'            => 'nullable|exists:production_sectors,id',
            'work_order_id'        => 'nullable|exists:work_orders,id',
            'started_at'           => 'required|date',
            'ended_at'             => 'nullable|date|after:started_at',
            'category'             => 'required|in:' . implode(',', array_keys(ProductionLoss::$categories)),
            'description'          => 'nullable|string|max:2000',
            'trafo'                => 'nullable|string|max:100',
            'inverter'             => 'nullable|string|max:100',
            'string_info'          => 'nullable|string|max:200',
            'affected_strings'     => 'nullable|integer|min:0',
            'affected_capacity_kw' => 'nullable|numeric|min:0',
            'lop_kwh'              => 'nullable|numeric|min:0',
        ]);

        $started     = Carbon::parse($validated['started_at']);
        $ended       = !empty($validated['ended_at']) ? Carbon::parse($validated['ended_at']) : null;
        $durationMin = $ended ? (int) $started->diffInMinutes($ended) : 0;

        $lopKwh = $validated['lop_kwh'] ?? null;
        if (!$lopKwh && !empty($validated['affected_capacity_kw']) && $durationMin > 0) {
            $lopKwh = round($validated['affected_capacity_kw'] * ($durationMin / 60), 2);
        }

        $productionLoss->update([
            'sector_id'            => $validated['sector_id'] ?? null,
            'work_order_id'        => $validated['work_order_id'] ?? null,
            'started_at'           => $started,
            'ended_at'             => $ended,
            'duration_minutes'     => $durationMin,
            'trafo'                => $validated['trafo'] ?? null,
            'inverter'             => $validated['inverter'] ?? null,
            'string_info'          => $validated['string_info'] ?? null,
            'affected_strings'     => $validated['affected_strings'] ?? null,
            'category'             => $validated['category'],
            'description'          => $validated['description'] ?? null,
            'affected_capacity_kw' => $validated['affected_capacity_kw'] ?? null,
            'lop_kwh'              => $lopKwh,
        ]);

        return redirect()->route('production-losses.index')
            ->with('success', 'Data Loss of Production berhasil diperbarui.');
    }

    public function destroy(ProductionLoss $productionLoss)
    {
        $user = auth()->user();

        if (!$user->isAdmin() && $productionLoss->location_id != $user->location_id) {
            abort(403);
        }

        $productionLoss->delete();

        return redirect()->route('production-losses.index')
            ->with('success', 'Data Loss of Production berhasil dihapus.');
    }
}
