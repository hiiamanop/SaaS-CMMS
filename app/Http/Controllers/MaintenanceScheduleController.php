<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceSchedule;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = MaintenanceSchedule::with(['technician', 'location']);

        // Non-admin hanya bisa lihat jadwal di lokasinya
        if (!$user->isAdmin()) {
            $query->where('location_id', $user->location_id);
        }

        if ($request->category)   $query->where('category', $request->category);
        if ($request->location_id) $query->where('location_id', $request->location_id);
        if ($request->status)     $query->where('status', $request->status);
        if ($request->filter === 'overdue') $query->where('next_due_date', '<', now())->where('status', 'active');
        if ($request->month)      $query->whereMonth('next_due_date', $request->month);
        if ($request->year)       $query->whereYear('next_due_date', $request->year);
        if ($request->date_from)  $query->whereDate('next_due_date', '>=', $request->date_from);
        if ($request->date_to)    $query->whereDate('next_due_date', '<=', $request->date_to);

        $schedules    = $query->orderBy('category')->orderBy('equipment_name')->get();
        $locations    = Location::where('is_active', true)->orderBy('name')->get();
        $categories   = MaintenanceSchedule::distinct()->pluck('category')->filter()->sort()->values();
        $overdueCount = MaintenanceSchedule::when(!$user->isAdmin(), fn($q) => $q->where('location_id', $user->location_id))
                            ->where('next_due_date', '<', now())->where('status', 'active')->count();

        $calendarQuery = MaintenanceSchedule::where('status', 'active');
        if (!$user->isAdmin()) $calendarQuery->where('location_id', $user->location_id);
        $calendarEvents = $calendarQuery->get()->map(function ($s) {
            return [
                'id'    => $s->id,
                'title' => $s->equipment_name ?: $s->title,
                'start' => $s->next_due_date->format('Y-m-d'),
                'color' => '#3b82f6',
                'url'   => route('maintenance-schedules.show', $s->id),
                'extendedProps' => ['type' => 'preventive'],
            ];
        });

        return view('maintenance-schedules.index', compact(
            'schedules', 'locations', 'categories', 'overdueCount', 'calendarEvents'
        ));
    }

    public function create()
    {
        $technicians  = User::whereIn('role', ['technician', 'supervisor'])->orderBy('name')->get();
        $locations    = Location::where('is_active', true)->orderBy('name')->get();
        $userLocation = Auth::user()->location;
        return view('maintenance-schedules.create', compact('technicians', 'locations', 'userLocation'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'location_id'               => $user->isAdmin() ? 'required|exists:locations,id' : 'nullable',
            'technician_id'             => 'nullable|exists:users,id',
            'category'                  => 'nullable|string|max:255',
            'trafo_name'                => 'required|string|max:255',
            'item_pekerjaan'            => 'required|array|min:1',
            'item_pekerjaan.*.lokasi_inspeksi' => 'nullable|string|max:255',
            'item_pekerjaan.*.name'     => 'required|string|max:500',
            'item_pekerjaan.*.metode'   => 'nullable|string|max:500',
            'item_pekerjaan.*.standar'  => 'nullable|string|max:500',
            'frequency'                 => 'required|in:weekly,monthly,quarterly,annually',
            'shutdown_required'         => 'nullable|boolean',
            'shutdown_duration_hours'   => 'nullable|integer|min:1',
            'notes'                     => 'nullable|string',
            'planned_weeks'             => 'nullable|array',
        ]);

        // Non-admin: paksa pakai lokasi sendiri
        if (!$user->isAdmin()) {
            $validated['location_id'] = $user->location_id;
        }

        $trafoName = $validated['trafo_name'] ?? '';
        $validated['equipment_name']    = $trafoName ?: ($validated['category'] ?? '');
        $validated['type']              = 'preventive';
        
        $locationName   = \App\Models\Location::find($validated['location_id'])?->name ?? 'Unknown Location';
        $technicianName = \App\Models\User::find($validated['technician_id'] ?? null)?->name ?? 'Unassigned';
        $freqTitle      = ucfirst($validated['frequency']);
        $titleString    = "{$locationName} - {$trafoName} - {$technicianName} - {$freqTitle}";
        
        $validated['title']             = \Illuminate\Support\Str::limit($titleString, 250);
        $validated['status']            = 'active';
        $validated['start_date']        = now()->toDateString();
        $validated['next_due_date']     = now()->toDateString();
        $validated['shutdown_required'] = $request->boolean('shutdown_required');
        $validated['item_pekerjaan']    = array_values($validated['item_pekerjaan']);

        $planned = [];
        foreach ($request->input('planned_weeks', []) as $key => $val) {
            [$month, $week] = explode('_', $key);
            $planned[] = ['month' => (int) $month, 'week' => (int) $week];
        }
        $validated['planned_weeks'] = $planned ?: null;

        $schedule = MaintenanceSchedule::create($validated);
        $count = $schedule->generateYearSessions();

        return redirect()->route('maintenance-schedules.show', $schedule)
            ->with('success', "Jadwal berhasil dibuat. {$count} sesi auto-generated.");
    }

    public function show(MaintenanceSchedule $maintenanceSchedule)
    {
        $maintenanceSchedule->load(['technician', 'workOrders.assignedTo', 'location', 'checksheetSessions.submittedBy']);
        return view('maintenance-schedules.show', compact('maintenanceSchedule'));
    }

    public function edit(MaintenanceSchedule $maintenanceSchedule)
    {
        $technicians  = User::whereIn('role', ['technician', 'supervisor'])->orderBy('name')->get();
        $locations    = Location::where('is_active', true)->orderBy('name')->get();
        $userLocation = Auth::user()->location;
        return view('maintenance-schedules.edit', compact('maintenanceSchedule', 'technicians', 'locations', 'userLocation'));
    }

    public function update(Request $request, MaintenanceSchedule $maintenanceSchedule)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'location_id'               => $user->isAdmin() ? 'required|exists:locations,id' : 'nullable',
            'technician_id'             => 'nullable|exists:users,id',
            'category'                  => 'nullable|string|max:255',
            'trafo_name'                => 'required|string|max:255',
            'item_pekerjaan'            => 'required|array|min:1',
            'item_pekerjaan.*.lokasi_inspeksi' => 'nullable|string|max:255',
            'item_pekerjaan.*.name'     => 'required|string|max:500',
            'item_pekerjaan.*.metode'   => 'nullable|string|max:500',
            'item_pekerjaan.*.standar'  => 'nullable|string|max:500',
            'frequency'                 => 'required|in:weekly,monthly,quarterly,annually',
            'status'                    => 'required|in:active,inactive',
            'shutdown_required'         => 'nullable|boolean',
            'shutdown_duration_hours'   => 'nullable|integer|min:1',
            'notes'                     => 'nullable|string',
            'planned_weeks'             => 'nullable|array',
        ]);

        if (!$user->isAdmin()) {
            $validated['location_id'] = $user->location_id;
        }

        $trafoName = $validated['trafo_name'] ?? '';
        $validated['equipment_name']    = $trafoName ?: ($validated['category'] ?? '');
        $validated['type']              = 'preventive';
        
        $locationName   = \App\Models\Location::find($validated['location_id'] ?? $maintenanceSchedule->location_id)?->name ?? 'Unknown Location';
        $technicianName = \App\Models\User::find($validated['technician_id'] ?? null)?->name ?? 'Unassigned';
        $freqTitle      = ucfirst($validated['frequency']);
        $titleString    = "{$locationName} - {$trafoName} - {$technicianName} - {$freqTitle}";
        
        $validated['title']             = \Illuminate\Support\Str::limit($titleString, 250);
        $validated['shutdown_required'] = $request->boolean('shutdown_required');
        $validated['item_pekerjaan']    = array_values($validated['item_pekerjaan']);

        $planned = [];
        foreach ($request->input('planned_weeks', []) as $key => $val) {
            [$month, $week] = explode('_', $key);
            $planned[] = ['month' => (int) $month, 'week' => (int) $week];
        }
        $validated['planned_weeks'] = $planned ?: null;

        $maintenanceSchedule->update($validated);
        $count = $maintenanceSchedule->generateYearSessions();

        $msg = 'Jadwal berhasil diperbarui.';
        if ($count > 0) $msg .= " {$count} sesi baru auto-generated.";

        return redirect()->route('maintenance-schedules.show', $maintenanceSchedule)
            ->with('success', $msg);
    }

    public function destroy(MaintenanceSchedule $maintenanceSchedule)
    {
        $maintenanceSchedule->update(['deleted_by' => Auth::id()]);
        $maintenanceSchedule->delete();
        return redirect()->route('maintenance-schedules.index')
            ->with('success', 'Jadwal maintenance dihapus.');
    }
}
