<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\WorkOrderActivityLog;

use App\Models\Asset;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkOrder::with(['asset', 'assignedTo', 'createdBy']);

        if ($request->filter === 'overdue') {
            $query->whereNotIn('status', ['closed'])->where('due_date', '<', now());
        } elseif ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->priority) $query->where('priority', $request->priority);
        if ($request->asset_id) $query->where('asset_id', $request->asset_id);
        if ($request->assigned_to) {
            $query->whereHas('assignees', function($q) use ($request) {
                $q->where('users.id', $request->assigned_to);
            });
        }
        if ($request->date_from) $query->where('due_date', '>=', $request->date_from);
        if ($request->date_to) $query->where('due_date', '<=', $request->date_to);
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                  ->orWhere('wo_number', 'like', '%'.$request->search.'%');
            });
        }

        $workOrders = $query->latest()->paginate(15)->withQueryString();

        // Maintenance Records Query for Records Tab
        $queryRecord = \App\Models\MaintenanceRecord::with(['asset', 'technician', 'workOrder']);
        if ($request->search) {
            $queryRecord->where(function($q) use ($request) {
                $q->where('record_number', 'like', '%'.$request->search.'%')
                  ->orWhere('findings', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->asset_id) $queryRecord->where('asset_id', $request->asset_id);
        if ($request->date_from) $queryRecord->where('maintenance_date', '>=', $request->date_from);
        if ($request->date_to) $queryRecord->where('maintenance_date', '<=', $request->date_to);

        $records = $queryRecord->latest('maintenance_date')->paginate(15, ['*'], 'records_page')->withQueryString();

        $assets = Asset::orderBy('name')->get();
        $technicians = User::where('role', 'technician')->get();

        return view('work-orders.index', compact('workOrders', 'records', 'assets', 'technicians'));
    }

    public function create(Request $request)
    {
        $assets = Asset::where('status', 'active')->orderBy('name')->get();
        $technicians = User::where('role', 'technician')->get();
        return view('work-orders.create', compact('assets', 'technicians'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'asset_id' => 'required_if:is_external_client,0|nullable|exists:assets,id',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:users,id',
            'type' => 'required|in:corrective,preventive',
            'priority' => 'required|in:low,medium,high,critical',
            'due_date' => 'required|date',
            'is_external_client' => 'nullable|boolean',
            'client_name' => 'required_if:is_external_client,1|nullable|string|max:255',
            'description' => 'nullable|string',
            'shutdown_required' => 'nullable|boolean',
        ]);

        $validated['wo_number'] = WorkOrder::generateNumber();
        $validated['created_by'] = auth()->id();
        $validated['order_date'] = now();
        $validated['is_external_client'] = $request->boolean('is_external_client');
        if ($validated['is_external_client']) {
            $validated['asset_id'] = null;
        }
        $validated['shutdown_required'] = $request->boolean('shutdown_required');
        
        $assigneeIds = $validated['assigned_to'] ?? [];
        // Use first one as primary for compatibility
        $validated['assigned_to'] = !empty($assigneeIds) ? $assigneeIds[0] : null;

        $workOrder = WorkOrder::create($validated);
        
        if (!empty($assigneeIds)) {
            $workOrder->assignees()->sync($assigneeIds);
        }

        WorkOrderActivityLog::create([
            'work_order_id' => $workOrder->id,
            'user_id' => auth()->id(),
            'from_status' => null,
            'to_status' => 'open',
            'notes' => 'Work order created',
        ]);

        if (!empty($assigneeIds)) {
            foreach ($assigneeIds as $userId) {
                Notification::create([
                    'user_id' => $userId,
                    'type' => 'new_wo',
                    'title' => 'New Work Order Assigned',
                    'message' => "You have been assigned Work Order {$workOrder->wo_number}: {$workOrder->title}",
                    'url' => '/work-orders/'.$workOrder->id,
                ]);
            }
        }

        return redirect()->route('work-orders.show', $workOrder)->with('success', 'Work order created successfully.');
    }

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load(['asset', 'assignees', 'createdBy', 'checklistItems.checkedBy', 'activityLogs.user', 'maintenanceRecord']);
        $technicians = User::where('role', 'technician')->get();
        return view('work-orders.show', compact('workOrder', 'technicians'));
    }

    public function edit(WorkOrder $workOrder)
    {
        $assets = Asset::orderBy('name')->get();
        $technicians = User::where('role', 'technician')->get();
        return view('work-orders.edit', compact('workOrder', 'assets', 'technicians'));
    }

    public function update(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'asset_id' => 'required_if:is_external_client,0|nullable|exists:assets,id',
            'assigned_to' => 'nullable|array',
            'assigned_to.*' => 'exists:users,id',
            'type' => 'required|in:corrective,preventive',
            'priority' => 'required|in:low,medium,high,critical',
            'due_date' => 'required|date',
            'is_external_client' => 'nullable|boolean',
            'client_name' => 'required_if:is_external_client,1|nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['is_external_client'] = $request->boolean('is_external_client');
        if ($validated['is_external_client']) {
            $validated['asset_id'] = null;
        } else {
            $validated['client_name'] = null;
        }

        $oldAssigneeIds = $workOrder->assignees->pluck('id')->toArray();
        $newAssigneeIds = $validated['assigned_to'] ?? [];
        
        // Update primary assignee for compatibility
        $validated['assigned_to'] = !empty($newAssigneeIds) ? $newAssigneeIds[0] : null;
        
        $workOrder->update($validated);
        $workOrder->assignees()->sync($newAssigneeIds);

        $addedAssignees = array_diff($newAssigneeIds, $oldAssigneeIds);

        foreach ($addedAssignees as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type' => 'new_wo',
                'title' => 'Work Order Assigned to You',
                'message' => "Work Order {$workOrder->wo_number}: {$workOrder->title} has been assigned to you",
                'url' => '/work-orders/'.$workOrder->id,
            ]);
        }

        return redirect()->route('work-orders.show', $workOrder)->with('success', 'Work order updated.');
    }

    public function destroy(WorkOrder $workOrder)
    {
        $workOrder->delete();
        return redirect()->route('work-orders.index')->with('success', 'Work order deleted.');
    }

    public function updateStatus(Request $request, WorkOrder $workOrder)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,canceled,closed',
            'notes' => 'required_if:status,canceled|nullable|string',
        ], [
            'notes.required_if' => 'Catatan wajib diisi jika membatalkan Work Order.'
        ]);

        $oldStatus = $workOrder->status;
        $newStatus = $request->status;

        $updates = ['status' => $newStatus];
        if ($newStatus === 'in_progress' && !$workOrder->started_at) {
            $updates['started_at'] = now();
        }
        if ($newStatus === 'closed' && !$workOrder->completed_at) {
            $updates['completed_at'] = now();
        }

        $workOrder->update($updates);

        WorkOrderActivityLog::create([
            'work_order_id' => $workOrder->id,
            'user_id' => auth()->id(),
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'notes' => $request->notes,
        ]);

        if ($workOrder->created_by !== auth()->id()) {
            Notification::create([
                'user_id' => $workOrder->created_by,
                'type' => 'status_changed',
                'title' => 'Work Order Status Updated',
                'message' => "Work Order {$workOrder->wo_number} status changed from ".ucfirst($oldStatus)." to ".ucfirst(str_replace('_', ' ', $newStatus))." by ".auth()->user()->name,
                'url' => '/work-orders/'.$workOrder->id,
            ]);
        }

        if ($newStatus === 'closed') {
            return redirect()->route('maintenance-records.create', ['work_order_id' => $workOrder->id])
                ->with('success', 'Work order closed. Please create a maintenance record.');
        }

        return back()->with('success', 'Status updated successfully.');
    }



    public function myJobs()
    {
        $workOrders = WorkOrder::with(['asset'])
            ->whereHas('assignees', function($q) {
                $q->where('users.id', auth()->id());
            })
            ->whereNotIn('status', ['closed'])
            ->latest()
            ->paginate(15);
        return view('work-orders.my-jobs', compact('workOrders'));
    }
}
