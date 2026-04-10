<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\WorkOrderActivityLog;
use App\Models\WorkOrderChecklistItem;
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
        if ($request->assigned_to) $query->where('assigned_to', $request->assigned_to);
        if ($request->date_from) $query->where('due_date', '>=', $request->date_from);
        if ($request->date_to) $query->where('due_date', '<=', $request->date_to);
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                  ->orWhere('wo_number', 'like', '%'.$request->search.'%');
            });
        }

        $workOrders = $query->latest()->paginate(15)->withQueryString();
        $assets = Asset::orderBy('name')->get();
        $technicians = User::where('role', 'technician')->get();

        return view('work-orders.index', compact('workOrders', 'assets', 'technicians'));
    }

    public function create()
    {
        $assets = Asset::where('status', 'active')->orderBy('name')->get();
        $technicians = User::where('role', 'technician')->get();
        return view('work-orders.create', compact('assets', 'technicians'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'asset_id' => 'required|exists:assets,id',
            'assigned_to' => 'nullable|exists:users,id',
            'type' => 'required|in:corrective',
            'priority' => 'required|in:low,medium,high,critical',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'shutdown_required' => 'nullable|boolean',
            'checklist' => 'nullable|array',
            'checklist.*' => 'nullable|string',
        ]);

        $validated['wo_number'] = WorkOrder::generateNumber();
        $validated['created_by'] = auth()->id();
        $validated['shutdown_required'] = $request->boolean('shutdown_required');
        unset($validated['checklist']);

        $workOrder = WorkOrder::create($validated);

        WorkOrderActivityLog::create([
            'work_order_id' => $workOrder->id,
            'user_id' => auth()->id(),
            'from_status' => null,
            'to_status' => 'open',
            'notes' => 'Work order created',
        ]);

        if ($request->checklist) {
            foreach (array_filter($request->checklist) as $i => $item) {
                WorkOrderChecklistItem::create([
                    'work_order_id' => $workOrder->id,
                    'description' => $item,
                    'order' => $i + 1,
                ]);
            }
        }

        if ($workOrder->assigned_to) {
            Notification::create([
                'user_id' => $workOrder->assigned_to,
                'type' => 'new_wo',
                'title' => 'New Work Order Assigned',
                'message' => "You have been assigned Work Order {$workOrder->wo_number}: {$workOrder->title}",
                'url' => '/work-orders/'.$workOrder->id,
            ]);
        }

        return redirect()->route('work-orders.show', $workOrder)->with('success', 'Work order created successfully.');
    }

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load(['asset', 'assignedTo', 'createdBy', 'checklistItems.checkedBy', 'activityLogs.user', 'maintenanceRecord']);
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
            'asset_id' => 'required|exists:assets,id',
            'assigned_to' => 'nullable|exists:users,id',
            'type' => 'required|in:corrective',
            'priority' => 'required|in:low,medium,high,critical',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $oldAssignee = $workOrder->assigned_to;
        $workOrder->update($validated);

        if ($validated['assigned_to'] && $validated['assigned_to'] != $oldAssignee) {
            Notification::create([
                'user_id' => $validated['assigned_to'],
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
            'status' => 'required|in:open,in_progress,pending_review,closed',
            'notes' => 'nullable|string',
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

    public function toggleChecklist(WorkOrder $workOrder, WorkOrderChecklistItem $item)
    {
        $item->update([
            'is_checked' => !$item->is_checked,
            'checked_by' => auth()->id(),
            'checked_at' => now(),
        ]);
        return back()->with('success', 'Checklist updated.');
    }

    public function myJobs()
    {
        $workOrders = WorkOrder::with(['asset'])
            ->where('assigned_to', auth()->id())
            ->whereNotIn('status', ['closed'])
            ->latest()
            ->paginate(15);
        return view('work-orders.my-jobs', compact('workOrders'));
    }
}
