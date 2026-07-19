<?php

namespace App\Services;

use App\Models\ChecksheetSession;
use App\Models\Finding;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceRecordConsumable;
use App\Models\MaintenanceRecordPart;
use App\Models\MaintenanceRecordTool;
use App\Models\WorkOrder;

class ReportService
{
    public static function forMonth(int $year, int $month): array
    {
        $workOrders = WorkOrder::with('asset')
            ->whereYear('order_date', $year)->whereMonth('order_date', $month)
            ->get();

        $records = MaintenanceRecord::with(['asset', 'technician'])
            ->whereYear('maintenance_date', $year)->whereMonth('maintenance_date', $month)
            ->get();

        $checksheets = ChecksheetSession::with('schedule')
            ->whereNotNull('submitted_at')
            ->whereYear('submitted_at', $year)->whereMonth('submitted_at', $month)
            ->get();

        $findings = Finding::whereYear('found_date', $year)->whereMonth('found_date', $month)->get();

        $recordIds = $records->pluck('id');

        $spareParts = MaintenanceRecordPart::with('sparePart')
            ->whereIn('maintenance_record_id', $recordIds)->get()
            ->groupBy('spare_part_id')
            ->map(fn ($rows) => [
                'name'  => $rows->first()->sparePart->name ?? '—',
                'unit'  => $rows->first()->sparePart->unit ?? '',
                'qty'   => $rows->sum('qty_used'),
                'value' => $rows->sum(fn ($r) => $r->qty_used * ($r->unit_price ?? 0)),
            ])->values();

        $consumables = MaintenanceRecordConsumable::with('consumable')
            ->whereIn('maintenance_record_id', $recordIds)->get()
            ->groupBy('consumable_id')
            ->map(fn ($rows) => [
                'name'  => $rows->first()->consumable->name ?? '—',
                'unit'  => $rows->first()->consumable->unit ?? '',
                'qty'   => $rows->sum('qty_used'),
                'value' => $rows->sum(fn ($r) => $r->qty_used * ($r->unit_price ?? 0)),
            ])->values();

        $tools = MaintenanceRecordTool::with('tool')
            ->whereIn('maintenance_record_id', $recordIds)->get()
            ->groupBy('tool_id')
            ->map(fn ($rows) => [
                'name'  => $rows->first()->tool->name ?? '—',
                'count' => $rows->count(),
            ])->values();

        return compact('workOrders', 'records', 'checksheets', 'findings', 'spareParts', 'consumables', 'tools');
    }
}
