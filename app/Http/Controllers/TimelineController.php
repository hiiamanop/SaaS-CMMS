<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceSchedule;
use App\Models\ChecksheetSession;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->get('search');
        $workType = $request->get('work_type', 'all');

        $query_wo  = WorkOrder::withTrashed()->with(['asset', 'assignedTo']);
        $query_mr  = MaintenanceRecord::withTrashed()->with(['asset', 'technician']);
        $query_cs  = ChecksheetSession::with(['schedule' => fn($q) => $q->withTrashed()], 'submittedBy')
            ->where('status', 'submitted');
        $query_sch = MaintenanceSchedule::onlyTrashed()->with(['technician', 'location', 'deletedBy']);

        // Search Filter
        if ($search) {
            $query_wo->where(function($q) use ($search) {
                $q->where('wo_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('asset', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
            $query_mr->where(function($q) use ($search) {
                $q->where('record_number', 'like', "%{$search}%")
                  ->orWhereHas('asset', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
            $query_cs->where(function($q) use ($search) {
                $q->where('plts_location', 'like', "%{$search}%")
                  ->orWhere('equipment_location', 'like', "%{$search}%")
                  ->orWhere('period_label', 'like', "%{$search}%");
            });
            $query_sch->where(function($q) use ($search) {
                $q->where('equipment_name', 'like', "%{$search}%")
                  ->orWhereHas('location', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
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
            $query_sch->where('deleted_at', '<=', $request->date_to.' 23:59:59');
        }

        $workOrders = ($workType === 'all' || $workType === 'work_order')
            ? $query_wo->latest()->take(100)->get()->map(function($wo) {
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
            })
            : collect();

        $maintenanceRecords = ($workType === 'all' || $workType === 'maint_schedule')
            ? $query_mr->latest('maintenance_date')->take(100)->get()->map(function($mr) {
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
            })
            : collect();

        $checksheets = ($workType === 'all' || $workType === 'maint_schedule')
            ? $query_cs->latest('submitted_at')->take(100)->get()->map(function($cs) {
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
            })
            : collect();

        $deletedSchedules = ($workType === 'all' || $workType === 'maint_schedule')
            ? $query_sch->get()->map(function($sch) {
                return [
                    'id'       => $sch->id,
                    'type'     => 'maintenance_schedule',
                    'title'    => 'Jadwal Dihapus: '.($sch->title ?: $sch->equipment_name),
                    'asset'    => $sch->location?->name ?? '—',
                    'person'   => $sch->technician?->name ?? '—',
                    'executor' => $sch->deletedBy?->name ?? 'System',
                    'status'   => 'deleted',
                    'wo_type'  => 'system',
                    'priority' => null,
                    'date'     => $sch->deleted_at,
                    'url'      => null,
                    'deleted'  => true,
                ];
            })
            : collect();

        $timeline = $workOrders->concat($maintenanceRecords)->concat($checksheets)->concat($deletedSchedules)->sortByDesc('date')->values();

        $calendarEvents = $timeline->map(function($item) {
            $color = match(true) {
                $item['type'] === 'checksheet' => '#14b8a6',
                $item['status'] === 'deleted' => '#ef4444',
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

        return view('timeline.index', compact('timeline', 'calendarEvents'));
    }
}
