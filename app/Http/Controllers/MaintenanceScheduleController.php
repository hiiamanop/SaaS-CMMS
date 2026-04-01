<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceSchedule;
use App\Models\ScheduleChecklistItem;
use App\Models\Asset;
use Illuminate\Http\Request;

class MaintenanceScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceSchedule::with('asset');

        if ($request->asset_id) $query->where('asset_id', $request->asset_id);
        if ($request->type) $query->where('type', $request->type);
        if ($request->status) $query->where('status', $request->status);
        if ($request->filter === 'overdue') $query->where('next_due_date', '<', now())->where('status', 'active');

        $schedules = $query->orderBy('next_due_date')->paginate(15)->withQueryString();
        $assets = Asset::orderBy('name')->get();
        $overdueCount = MaintenanceSchedule::where('next_due_date', '<', now())->where('status', 'active')->count();

        $calendarEvents = MaintenanceSchedule::with('asset')->where('status', 'active')->get()->map(function($s) {
            return [
                'id' => $s->id,
                'title' => $s->title,
                'start' => $s->next_due_date->format('Y-m-d'),
                'color' => $s->type === 'preventive' ? '#3b82f6' : '#f97316',
                'url' => route('maintenance-schedules.show', $s->id),
                'extendedProps' => ['asset' => $s->asset->name, 'type' => $s->type],
            ];
        });

        return view('maintenance-schedules.index', compact('schedules', 'assets', 'overdueCount', 'calendarEvents'));
    }

    public function create()
    {
        $assets = Asset::orderBy('name')->get();
        return view('maintenance-schedules.create', compact('assets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'asset_id' => 'required|exists:assets,id',
            'type' => 'required|in:preventive,corrective',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,annually,custom',
            'frequency_days' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'notes' => 'nullable|string',
            'checklist' => 'nullable|array',
            'checklist.*' => 'nullable|string',
        ]);

        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        $validated['next_due_date'] = $startDate->toDateString();
        unset($validated['checklist']);

        $schedule = MaintenanceSchedule::create($validated);

        if ($request->checklist) {
            foreach (array_filter($request->checklist) as $i => $item) {
                ScheduleChecklistItem::create([
                    'maintenance_schedule_id' => $schedule->id,
                    'description' => $item,
                    'order' => $i + 1,
                ]);
            }
        }

        return redirect()->route('maintenance-schedules.index')->with('success', 'Schedule created successfully.');
    }

    public function show(MaintenanceSchedule $maintenanceSchedule)
    {
        $maintenanceSchedule->load(['asset', 'checklistItems', 'workOrders.assignedTo']);
        return view('maintenance-schedules.show', compact('maintenanceSchedule'));
    }

    public function edit(MaintenanceSchedule $maintenanceSchedule)
    {
        $maintenanceSchedule->load('checklistItems');
        $assets = Asset::orderBy('name')->get();
        return view('maintenance-schedules.edit', compact('maintenanceSchedule', 'assets'));
    }

    public function update(Request $request, MaintenanceSchedule $maintenanceSchedule)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'asset_id' => 'required|exists:assets,id',
            'type' => 'required|in:preventive,corrective',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,annually,custom',
            'frequency_days' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'next_due_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
            'checklist' => 'nullable|array',
            'checklist.*' => 'nullable|string',
        ]);

        unset($validated['checklist']);
        $maintenanceSchedule->update($validated);

        $maintenanceSchedule->checklistItems()->delete();
        if ($request->checklist) {
            foreach (array_filter($request->checklist) as $i => $item) {
                ScheduleChecklistItem::create([
                    'maintenance_schedule_id' => $maintenanceSchedule->id,
                    'description' => $item,
                    'order' => $i + 1,
                ]);
            }
        }

        return redirect()->route('maintenance-schedules.show', $maintenanceSchedule)->with('success', 'Schedule updated.');
    }

    public function destroy(MaintenanceSchedule $maintenanceSchedule)
    {
        $maintenanceSchedule->delete();
        return redirect()->route('maintenance-schedules.index')->with('success', 'Schedule deleted.');
    }
}
