<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\ProductionEntry;
use App\Models\ProductionReport;
use App\Models\ProductionSector;
use App\Models\ProductionTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year', now()->year);

        $query = ProductionReport::with(['location', 'creator', 'entries.sector'])
            ->whereMonth('report_date', $month)
            ->whereYear('report_date', $year)
            ->orderBy('report_date', 'desc');

        if (!$user->isAdmin()) {
            $query->where('location_id', $user->location_id);
        }

        $reports = $query->get();

        $locations = $user->isAdmin()
            ? Location::orderBy('name')->get()
            : Location::where('id', $user->location_id)->get();

        return view('production-reports.index', compact('reports', 'locations', 'month', 'year'));
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

        return view('production-reports.create', compact('locations', 'locationId', 'sectors'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'location_id'    => 'required|exists:locations,id',
            'report_date'    => 'required|date',
            'cerah_hours'    => 'nullable|numeric|min:0|max:24',
            'berawan_hours'  => 'nullable|numeric|min:0|max:24',
            'mendung_hours'  => 'nullable|numeric|min:0|max:24',
            'hujan_hours'    => 'nullable|numeric|min:0|max:24',
            'notes'          => 'nullable|string|max:1000',
            'entries'        => 'nullable|array',
            'entries.*.sector_id'         => 'required|exists:production_sectors,id',
            'entries.*.kwh_trafo'         => 'nullable|numeric|min:0',
            'entries.*.kwh_meter'         => 'nullable|numeric|min:0',
            'entries.*.sun_hour'          => 'nullable|numeric|min:0|max:24',
            'entries.*.capacity_factor'   => 'nullable|numeric|min:0',
            'entries.*.performance_ratio' => 'nullable|numeric|min:0|max:2',
        ]);

        if (!$user->isAdmin() && $validated['location_id'] != $user->location_id) {
            abort(403);
        }

        DB::transaction(function () use ($validated, $user) {
            $report = ProductionReport::create([
                'location_id'   => $validated['location_id'],
                'created_by'    => $user->id,
                'report_date'   => $validated['report_date'],
                'cerah_hours'   => $validated['cerah_hours'] ?? 0,
                'berawan_hours' => $validated['berawan_hours'] ?? 0,
                'mendung_hours' => $validated['mendung_hours'] ?? 0,
                'hujan_hours'   => $validated['hujan_hours'] ?? 0,
                'notes'         => $validated['notes'] ?? null,
            ]);

            foreach ($validated['entries'] ?? [] as $entry) {
                if (is_null($entry['kwh_trafo']) && is_null($entry['kwh_meter']) && is_null($entry['sun_hour'])) {
                    continue;
                }

                [$cf, $pr] = $this->computeCfPr($entry);

                ProductionEntry::create([
                    'production_report_id' => $report->id,
                    'sector_id'            => $entry['sector_id'],
                    'kwh_trafo'            => $entry['kwh_trafo'] ?? null,
                    'kwh_meter'            => $entry['kwh_meter'] ?? null,
                    'sun_hour'             => $entry['sun_hour'] ?? null,
                    'capacity_factor'      => $cf,
                    'performance_ratio'    => $pr,
                ]);
            }
        });

        return redirect()->route('production-reports.index')
            ->with('success', 'Laporan produksi berhasil disimpan.');
    }

    public function show(ProductionReport $productionReport)
    {
        $productionReport->load(['location', 'creator', 'entries.sector']);

        // Load targets for this month
        $targets = ProductionTarget::where('location_id', $productionReport->location_id)
            ->where('year', $productionReport->report_date->year)
            ->where('month', $productionReport->report_date->month)
            ->whereNotNull('sector_id')
            ->get()
            ->keyBy('sector_id');

        return view('production-reports.show', compact('productionReport', 'targets'));
    }

    public function edit(ProductionReport $productionReport)
    {
        $user = auth()->user();

        if (!$user->isAdmin() && $productionReport->location_id != $user->location_id) {
            abort(403);
        }

        $productionReport->load(['entries.sector']);

        $locations = $user->isAdmin()
            ? Location::orderBy('name')->get()
            : Location::where('id', $user->location_id)->get();

        $sectors = ProductionSector::where('location_id', $productionReport->location_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $existingEntries = $productionReport->entries->keyBy('sector_id');

        return view('production-reports.edit', compact('productionReport', 'locations', 'sectors', 'existingEntries'));
    }

    public function update(Request $request, ProductionReport $productionReport)
    {
        $user = auth()->user();

        if (!$user->isAdmin() && $productionReport->location_id != $user->location_id) {
            abort(403);
        }

        $validated = $request->validate([
            'report_date'    => 'required|date',
            'cerah_hours'    => 'nullable|numeric|min:0|max:24',
            'berawan_hours'  => 'nullable|numeric|min:0|max:24',
            'mendung_hours'  => 'nullable|numeric|min:0|max:24',
            'hujan_hours'    => 'nullable|numeric|min:0|max:24',
            'notes'          => 'nullable|string|max:1000',
            'entries'        => 'nullable|array',
            'entries.*.sector_id'         => 'required|exists:production_sectors,id',
            'entries.*.kwh_trafo'         => 'nullable|numeric|min:0',
            'entries.*.kwh_meter'         => 'nullable|numeric|min:0',
            'entries.*.sun_hour'          => 'nullable|numeric|min:0|max:24',
            'entries.*.capacity_factor'   => 'nullable|numeric|min:0',
            'entries.*.performance_ratio' => 'nullable|numeric|min:0|max:2',
        ]);

        DB::transaction(function () use ($validated, $productionReport) {
            $productionReport->update([
                'report_date'   => $validated['report_date'],
                'cerah_hours'   => $validated['cerah_hours'] ?? 0,
                'berawan_hours' => $validated['berawan_hours'] ?? 0,
                'mendung_hours' => $validated['mendung_hours'] ?? 0,
                'hujan_hours'   => $validated['hujan_hours'] ?? 0,
                'notes'         => $validated['notes'] ?? null,
            ]);

            $productionReport->entries()->delete();

            foreach ($validated['entries'] ?? [] as $entry) {
                if (is_null($entry['kwh_trafo']) && is_null($entry['kwh_meter']) && is_null($entry['sun_hour'])) {
                    continue;
                }

                [$cf, $pr] = $this->computeCfPr($entry);

                ProductionEntry::create([
                    'production_report_id' => $productionReport->id,
                    'sector_id'            => $entry['sector_id'],
                    'kwh_trafo'            => $entry['kwh_trafo'] ?? null,
                    'kwh_meter'            => $entry['kwh_meter'] ?? null,
                    'sun_hour'             => $entry['sun_hour'] ?? null,
                    'capacity_factor'      => $cf,
                    'performance_ratio'    => $pr,
                ]);
            }
        });

        return redirect()->route('production-reports.show', $productionReport)
            ->with('success', 'Laporan produksi berhasil diperbarui.');
    }

    public function destroy(ProductionReport $productionReport)
    {
        $user = auth()->user();

        if (!$user->isAdmin() && $productionReport->location_id != $user->location_id) {
            abort(403);
        }

        $productionReport->delete();

        return redirect()->route('production-reports.index')
            ->with('success', 'Laporan produksi berhasil dihapus.');
    }

    public function performance(Request $request)
    {
        $user = auth()->user();

        $month      = (int) $request->get('month', now()->month);
        $year       = (int) $request->get('year', now()->year);
        $locationId = $request->get('location_id', $user->location_id ?? Location::first()?->id);

        $locations = $user->isAdmin()
            ? Location::orderBy('name')->get()
            : Location::where('id', $user->location_id)->get();

        if (!$user->isAdmin()) {
            $locationId = $user->location_id;
        }

        // All reports this month
        $reports = ProductionReport::with(['entries.sector'])
            ->where('location_id', $locationId)
            ->whereMonth('report_date', $month)
            ->whereYear('report_date', $year)
            ->orderBy('report_date')
            ->get();

        // Sectors for this location
        $sectors = ProductionSector::where('location_id', $locationId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Targets this month
        $targets = ProductionTarget::where('location_id', $locationId)
            ->where('year', $year)
            ->where('month', $month)
            ->get()
            ->keyBy('sector_id');

        // Build day-by-day data for chart + table
        $chartLabels = [];
        $chartKwh    = [];
        $chartPr     = [];
        $daysInMonth = now()->setDate($year, $month, 1)->daysInMonth;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date   = \Carbon\Carbon::create($year, $month, $d);
            $report = $reports->first(fn($r) => $r->report_date->day === $d);

            $chartLabels[] = $d;
            $chartKwh[]    = $report ? round($report->total_kwh_meter, 0) : null;

            // Average PR across sectors for the day
            if ($report && $report->entries->isNotEmpty()) {
                $validPr = $report->entries->whereNotNull('performance_ratio');
                $chartPr[] = $validPr->isNotEmpty()
                    ? round($validPr->avg('performance_ratio') * 100, 2)
                    : null;
            } else {
                $chartPr[] = null;
            }
        }

        // Monthly summary per sector
        $sectorSummary = [];
        foreach ($sectors as $sector) {
            $entries = $reports->flatMap(fn($r) => $r->entries->where('sector_id', $sector->id));
            $target  = $targets->get($sector->id);

            $totalKwh    = $entries->sum('kwh_meter');
            $totalDays   = $entries->where('kwh_meter', '>', 0)->count();
            $avgPr       = $entries->whereNotNull('performance_ratio')->avg('performance_ratio');
            $targetMonthly = $target ? $target->target_kwh_daily * $daysInMonth : null;
            $achievement   = ($targetMonthly && $totalKwh > 0)
                ? round($totalKwh / $targetMonthly * 100, 1)
                : null;

            $sectorSummary[] = [
                'sector'          => $sector,
                'total_kwh'       => $totalKwh,
                'days_recorded'   => $totalDays,
                'avg_pr'          => $avgPr,
                'target_daily'    => $target?->target_kwh_daily,
                'target_monthly'  => $targetMonthly,
                'target_pr'       => $target?->target_pr,
                'achievement_pct' => $achievement,
            ];
        }

        return view('production-reports.performance', compact(
            'reports', 'sectors', 'targets', 'locations', 'locationId',
            'month', 'year', 'sectorSummary', 'chartLabels', 'chartKwh', 'chartPr',
            'daysInMonth'
        ));
    }

    public function getSectors(Request $request)
    {
        $sectors = ProductionSector::where('location_id', $request->location_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'capacity_kwp', 'capacity_kwac']);

        return response()->json($sectors);
    }

    private function computeCfPr(array $entry): array
    {
        $kwp = 0;
        if (!empty($entry['sector_id'])) {
            $sector = ProductionSector::find($entry['sector_id']);
            $kwp = (float) ($sector?->capacity_kwp ?? 0);
        }

        $kwh = (float) ($entry['kwh_meter'] ?? 0);
        $sh  = (float) ($entry['sun_hour'] ?? 0);

        if ($kwp <= 0 || $sh <= 0 || $kwh <= 0) {
            return [
                $entry['capacity_factor'] ?? null,
                $entry['performance_ratio'] ?? null,
            ];
        }

        $computed = round($kwh / ($kwp * $sh), 4);

        // If user manually provided PR, use that; otherwise auto-compute from same formula
        $cf = $entry['capacity_factor'] ?? $computed;
        $pr = $entry['performance_ratio'] ?? $computed;

        return [$cf, $pr];
    }
}
