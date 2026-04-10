<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceSchedule;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Request;

class MaintenanceScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceSchedule::with(['asset', 'technician']);

        if ($request->asset_id)  $query->where('asset_id', $request->asset_id);
        if ($request->category)  $query->where('category', $request->category);
        if ($request->status)    $query->where('status', $request->status);
        if ($request->filter === 'overdue') $query->where('next_due_date', '<', now())->where('status', 'active');
        if ($request->month)     $query->whereMonth('next_due_date', $request->month);
        if ($request->year)      $query->whereYear('next_due_date', $request->year);
        if ($request->date_from) $query->whereDate('next_due_date', '>=', $request->date_from);
        if ($request->date_to)   $query->whereDate('next_due_date', '<=', $request->date_to);

        $schedules    = $query->orderBy('category')->orderBy('equipment_name')->get();
        $assets       = Asset::orderBy('name')->get();
        $categories   = MaintenanceSchedule::distinct()->pluck('category')->filter()->sort()->values();
        $overdueCount = MaintenanceSchedule::where('next_due_date', '<', now())->where('status', 'active')->count();

        $calendarEvents = MaintenanceSchedule::with('asset')->where('status', 'active')->get()->map(function ($s) {
            return [
                'id'    => $s->id,
                'title' => $s->equipment_name ?: $s->title,
                'start' => $s->next_due_date->format('Y-m-d'),
                'color' => '#3b82f6',
                'url'   => route('maintenance-schedules.show', $s->id),
                'extendedProps' => ['asset' => $s->asset->name, 'type' => 'preventive'],
            ];
        });

        return view('maintenance-schedules.index', compact(
            'schedules', 'assets', 'categories', 'overdueCount', 'calendarEvents'
        ));
    }

    public function create()
    {
        $assets      = Asset::orderBy('name')->get();
        $technicians = User::whereIn('role', ['technician', 'supervisor'])->orderBy('name')->get();
        return view('maintenance-schedules.create', compact('assets', 'technicians'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_name'          => 'required|string|max:255',
            'asset_id'                => 'required|exists:assets,id',
            'technician_id'           => 'nullable|exists:users,id',
            'category'                => 'required|string|max:255',
            'item_pekerjaan'          => 'required|array|min:1',
            'item_pekerjaan.*'        => 'required|string|max:500',
            'frequency'               => 'required|in:weekly,monthly,quarterly,annually',
            'shutdown_required'       => 'nullable|boolean',
            'shutdown_duration_hours' => 'nullable|integer|min:1',
            'notes'                   => 'nullable|string',
            'planned_weeks'           => 'nullable|array',
        ]);

        $validated['type']              = 'preventive';
        $validated['title']             = $validated['equipment_name'] . ' — ' . implode(', ', $validated['item_pekerjaan']);
        $validated['status']            = 'active';
        $validated['start_date']        = now()->toDateString();
        $validated['next_due_date']     = now()->toDateString();
        $validated['shutdown_required'] = $request->boolean('shutdown_required');

        $planned = [];
        foreach ($request->input('planned_weeks', []) as $key => $val) {
            [$month, $week] = explode('_', $key);
            $planned[] = ['month' => (int) $month, 'week' => (int) $week];
        }
        $validated['planned_weeks'] = $planned ?: null;

        $schedule = MaintenanceSchedule::create($validated);
        $schedule->load('asset');
        $count = $schedule->generateYearSessions();

        return redirect()->route('maintenance-schedules.show', $schedule)
            ->with('success', "Jadwal berhasil dibuat. {$count} sesi checksheet auto-generated. Silakan tambahkan template checksheet.");
    }

    public function show(MaintenanceSchedule $maintenanceSchedule)
    {
        $maintenanceSchedule->load(['asset', 'technician', 'checklistTemplates', 'workOrders.assignedTo']);
        return view('maintenance-schedules.show', compact('maintenanceSchedule'));
    }

    public function edit(MaintenanceSchedule $maintenanceSchedule)
    {
        $assets      = Asset::orderBy('name')->get();
        $technicians = User::whereIn('role', ['technician', 'supervisor'])->orderBy('name')->get();
        return view('maintenance-schedules.edit', compact('maintenanceSchedule', 'assets', 'technicians'));
    }

    public function update(Request $request, MaintenanceSchedule $maintenanceSchedule)
    {
        $validated = $request->validate([
            'equipment_name'          => 'required|string|max:255',
            'asset_id'                => 'required|exists:assets,id',
            'technician_id'           => 'nullable|exists:users,id',
            'category'                => 'required|string|max:255',
            'item_pekerjaan'          => 'required|array|min:1',
            'item_pekerjaan.*'        => 'required|string|max:500',
            'frequency'               => 'required|in:weekly,monthly,quarterly,annually',
            'status'                  => 'required|in:active,inactive',
            'shutdown_required'       => 'nullable|boolean',
            'shutdown_duration_hours' => 'nullable|integer|min:1',
            'notes'                   => 'nullable|string',
            'planned_weeks'           => 'nullable|array',
        ]);

        $validated['type']              = 'preventive';
        $validated['title']             = $validated['equipment_name'] . ' — ' . implode(', ', $validated['item_pekerjaan']);
        $validated['shutdown_required'] = $request->boolean('shutdown_required');

        $planned = [];
        foreach ($request->input('planned_weeks', []) as $key => $val) {
            [$month, $week] = explode('_', $key);
            $planned[] = ['month' => (int) $month, 'week' => (int) $week];
        }
        $validated['planned_weeks'] = $planned ?: null;

        $maintenanceSchedule->update($validated);
        $maintenanceSchedule->load('asset');
        $count = $maintenanceSchedule->generateYearSessions();

        $msg = 'Jadwal berhasil diperbarui.';
        if ($count > 0) $msg .= " {$count} sesi baru auto-generated.";

        return redirect()->route('maintenance-schedules.show', $maintenanceSchedule)
            ->with('success', $msg);
    }

    public function destroy(MaintenanceSchedule $maintenanceSchedule)
    {
        $maintenanceSchedule->delete();
        return redirect()->route('maintenance-schedules.index')
            ->with('success', 'Jadwal maintenance dihapus.');
    }
}
