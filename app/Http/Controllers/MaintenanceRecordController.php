<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRecord;
use App\Models\MaintenanceRecordPart;
use App\Models\MaintenanceRecordPhoto;
use App\Models\WorkOrder;
use App\Models\Asset;
use App\Models\SparePart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MaintenanceRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = MaintenanceRecord::with(['asset', 'technician', 'workOrder']);

        if ($request->asset_id) $query->where('asset_id', $request->asset_id);
        if ($request->technician_id) $query->where('technician_id', $request->technician_id);
        if ($request->type) $query->where('type', $request->type);
        if ($request->date_from) $query->where('maintenance_date', '>=', $request->date_from);
        if ($request->date_to) $query->where('maintenance_date', '<=', $request->date_to);
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('record_number', 'like', '%'.$request->search.'%')
                  ->orWhere('findings', 'like', '%'.$request->search.'%');
            });
        }

        $records = $query->latest('maintenance_date')->paginate(15)->withQueryString();
        $assets = Asset::orderBy('name')->get();
        $technicians = User::where('role', 'technician')->get();

        return view('maintenance-records.index', compact('records', 'assets', 'technicians'));
    }

    public function create(Request $request)
    {
        $workOrder = null;
        if ($request->work_order_id) {
            $workOrder = WorkOrder::with(['asset', 'assignedTo', 'checklistItems'])->find($request->work_order_id);
        }

        $workOrders = WorkOrder::with(['asset', 'assignedTo'])->latest()->get();
        $technicians = User::where('role', 'technician')->get();
        $spareParts = SparePart::orderBy('name')->get();

        return view('maintenance-records.create', compact('workOrder', 'workOrders', 'technicians', 'spareParts'));
    }

    public function store(Request $request)
    {
        // If a WO is selected, derive asset_id from it
        if ($request->filled('work_order_id') && !$request->filled('asset_id')) {
            $wo = WorkOrder::find($request->work_order_id);
            $request->merge(['asset_id' => $wo?->asset_id]);
        }

        $validated = $request->validate([
            'work_order_id' => 'nullable|exists:work_orders,id',
            'asset_id' => 'required|exists:assets,id',
            'technician_id' => 'required|exists:users,id',
            'type' => 'nullable|in:preventive,corrective',
            'maintenance_date' => 'required|date',
            'findings' => 'nullable|string',
            'actions_taken' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:0',
            'shutdown_minutes' => 'required|integer|min:0',
            'notes' => 'nullable|string',
            'parts' => 'nullable|array',
            'parts.*.spare_part_id' => 'required|exists:spare_parts,id',
            'parts.*.qty_used' => 'required|integer|min:1',
            'photos' => 'nullable|array',
            'photos.*' => 'image|max:5120',
        ]);

        DB::transaction(function() use ($validated, $request) {
            // Auto-derive type: if linked to WO → corrective; else fallback to submitted value or preventive
            $type = $validated['type'] ?? 'preventive';
            if (!empty($validated['work_order_id'])) {
                $type = 'corrective';
            }

            $record = MaintenanceRecord::create([
                'record_number' => MaintenanceRecord::generateNumber(),
                'work_order_id' => $validated['work_order_id'] ?? null,
                'asset_id' => $validated['asset_id'],
                'technician_id' => $validated['technician_id'],
                'type' => $type,
                'maintenance_date' => $validated['maintenance_date'],
                'findings' => $validated['findings'] ?? null,
                'actions_taken' => $validated['actions_taken'] ?? null,
                'duration_minutes' => $validated['duration_minutes'],
                'shutdown_minutes' => $validated['shutdown_minutes'],
                'notes' => $validated['notes'] ?? null,
            ]);

            if (!empty($validated['parts'])) {
                foreach ($validated['parts'] as $part) {
                    $sparePart = SparePart::find($part['spare_part_id']);
                    MaintenanceRecordPart::create([
                        'maintenance_record_id' => $record->id,
                        'spare_part_id' => $part['spare_part_id'],
                        'qty_used' => $part['qty_used'],
                        'unit_price' => $sparePart->unit_price,
                    ]);
                    $sparePart->decrement('qty_actual', $part['qty_used']);
                }
            }

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('maintenance-records', 'public');
                    MaintenanceRecordPhoto::create([
                        'maintenance_record_id' => $record->id,
                        'file_path' => $path,
                    ]);
                }
            }
        });

        return redirect()->route('maintenance-records.index')->with('success', 'Maintenance record created successfully.');
    }

    public function show(MaintenanceRecord $maintenanceRecord)
    {
        $maintenanceRecord->load(['asset', 'technician', 'workOrder', 'parts.sparePart', 'photos']);
        return view('maintenance-records.show', compact('maintenanceRecord'));
    }

    public function edit(MaintenanceRecord $maintenanceRecord)
    {
        $maintenanceRecord->load(['parts', 'photos']);
        $assets = Asset::orderBy('name')->get();
        $technicians = User::where('role', 'technician')->get();
        $spareParts = SparePart::orderBy('name')->get();
        return view('maintenance-records.edit', compact('maintenanceRecord', 'assets', 'technicians', 'spareParts'));
    }

    public function update(Request $request, MaintenanceRecord $maintenanceRecord)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'technician_id' => 'required|exists:users,id',
            'type' => 'required|in:preventive,corrective',
            'maintenance_date' => 'required|date',
            'findings' => 'nullable|string',
            'actions_taken' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:0',
            'shutdown_minutes' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $maintenanceRecord->update($validated);
        return redirect()->route('maintenance-records.show', $maintenanceRecord)->with('success', 'Record updated.');
    }

    public function destroy(MaintenanceRecord $maintenanceRecord)
    {
        foreach ($maintenanceRecord->photos as $photo) {
            Storage::disk('public')->delete($photo->file_path);
        }
        $maintenanceRecord->delete();
        return redirect()->route('maintenance-records.index')->with('success', 'Record deleted.');
    }
}
