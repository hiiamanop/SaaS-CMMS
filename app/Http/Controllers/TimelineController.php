<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\MaintenanceRecord;
use App\Models\ChecksheetSession;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    public function index(Request $request)
    {
        $query_wo = WorkOrder::withTrashed()->with(['asset', 'assignedTo']);
        $query_mr = MaintenanceRecord::withTrashed()->with(['asset', 'technician']);
        $query_cs = ChecksheetSession::with(['schedule.asset', 'submittedBy'])
            ->where('status', 'submitted');

        if ($request->asset_id) {
            $query_wo->where('asset_id', $request->asset_id);
            $query_mr->where('asset_id', $request->asset_id);
        }
        if ($request->technician_id) {
            $query_wo->where('assigned_to', $request->technician_id);
            $query_mr->where('technician_id', $request->technician_id);
            $query_cs->where('submitted_by', $request->technician_id);
        }
        if ($request->date_from) {
            $query_wo->where('created_at', '>=', $request->date_from);
            $query_mr->where('maintenance_date', '>=', $request->date_from);
            $query_cs->where('submitted_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query_wo->where('created_at', '<=', $request->date_to.' 23:59:59');
            $query_mr->where('maintenance_date', '<=', $request->date_to);
            $query_cs->where('submitted_at', '<=', $request->date_to.' 23:59:59');
        }

        $workOrders = $query_wo->get()->map(function($wo) {
            $deleted = $wo->trashed();
            return [
                'id'      => $wo->id,
                'type'    => 'work_order',
                'title'   => $wo->wo_number.': '.$wo->title,
                'asset'   => $wo->asset?->name ?? '—',
                'person'  => $wo->assignedTo?->name ?? 'Unassigned',
                'status'  => $wo->status,
                'wo_type' => $wo->type,
                'priority'=> $wo->priority,
                'date'    => $wo->created_at,
                'url'     => $deleted ? null : route('work-orders.show', $wo->id),
                'deleted' => $deleted,
            ];
        });

        $maintenanceRecords = $query_mr->get()->map(function($mr) {
            $deleted = $mr->trashed();
            return [
                'id'      => $mr->id,
                'type'    => 'maintenance_record',
                'title'   => $mr->record_number.': '.($mr->asset?->name ?? '—').' Maintenance',
                'asset'   => $mr->asset?->name ?? '—',
                'person'  => $mr->technician?->name ?? '—',
                'status'  => 'completed',
                'wo_type' => $mr->type,
                'priority'=> null,
                'date'    => $mr->maintenance_date,
                'url'     => $deleted ? null : route('maintenance-records.show', $mr->id),
                'deleted' => $deleted,
            ];
        });

        $checksheets = $query_cs->get()->map(function($cs) {
            return [
                'id'       => $cs->id,
                'type'     => 'checksheet',
                'title'    => 'Checksheet: '.$cs->equipment_location.' — '.$cs->period_label,
                'asset'    => $cs->plts_location,
                'person'   => $cs->submittedBy?->name ?? $cs->signed_by_teknisi ?? '—',
                'status'   => 'submitted',
                'wo_type'  => 'preventive',
                'priority' => null,
                'date'     => $cs->submitted_at,
                'url'      => route('checksheet.show', $cs->id),
                'deleted'  => false,
            ];
        });

        $timeline = $workOrders->concat($maintenanceRecords)->concat($checksheets)->sortByDesc('date')->values();

        $calendarEvents = $timeline->map(function($item) {
            $color = match(true) {
                $item['type'] === 'checksheet' => '#14b8a6',
                in_array($item['status'], ['completed', 'closed']) => '#10b981',
                str_starts_with($item['wo_type'] ?? '', 'preventive') => '#3b82f6',
                default => '#f97316',
            };
            return [
                'id'    => $item['type'].'_'.$item['id'],
                'title' => $item['title'],
                'start' => \Carbon\Carbon::parse($item['date'])->toDateString(),
                'url'   => $item['url'],
                'color' => $color,
                'extendedProps' => [
                    'asset'  => $item['asset'],
                    'person' => $item['person'],
                    'type'   => $item['type'],
                    'wo_type'=> $item['wo_type'],
                    'status' => $item['status'],
                ],
            ];
        })->values();

        $assets = Asset::orderBy('name')->get();
        $technicians = User::where('role', 'technician')->get();

        return view('timeline.index', compact('timeline', 'assets', 'technicians', 'calendarEvents'));
    }
}
