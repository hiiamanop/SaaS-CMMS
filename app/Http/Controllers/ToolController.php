<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    private function authorizeManager(): void
    {
        if (!auth()->user()->isAdminOrSupervisor()) {
            abort(403, 'Unauthorized.');
        }
    }

    public function index(Request $request)
    {
        $query = Tool::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('tool_code', 'like', '%'.$request->search.'%')
                  ->orWhere('brand', 'like', '%'.$request->search.'%');
            });
        }
        
        if ($request->condition) {
            $query->where('condition', $request->condition);
        }

        $tools = $query->latest()->paginate(15)->withQueryString();
        $conditions = ['good', 'damaged', 'lost'];

        return view('tools.index', compact('tools', 'conditions'));
    }

    public function exportCsv(Request $request)
    {
        $filename = 'tools_export_' . now()->format('Ymd_His') . '.csv';
        $query = Tool::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('tool_code', 'like', '%'.$request->search.'%')
                  ->orWhere('brand', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->condition) {
            $query->where('condition', $request->condition);
        }

        $tools = $query->orderBy('tool_code')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($tools) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Tool Code', 'Name', 'Brand', 'Category', 'Condition', 'Qty Available', 'Qty Total', 'Location', 'Description'], ';');

            foreach ($tools as $t) {
                fputcsv($handle, [
                    $t->tool_code,
                    $t->name,
                    $t->brand,
                    $t->category,
                    $t->condition,
                    $t->qty_available,
                    $t->qty_total,
                    $t->location,
                    $t->description,
                ], ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $this->authorizeManager();
        return view('tools.create');
    }

    public function show(Tool $tool)
    {
        $tool->load([
            'workOrderItems.workOrder',
            'workOrderItems.createdBy',
            'maintenanceRecordTools.maintenanceRecord.workOrder',
        ]);

        return view('tools.show', compact('tool'));
    }

    public function store(Request $request)
    {
        $this->authorizeManager();
        $validated = $request->validate([
            'tool_code'     => 'nullable|string|unique:tools',
            'name'          => 'required|string|max:255',
            'category'      => 'nullable|string',
            'brand'         => 'nullable|string',
            'condition'     => 'required|in:good,damaged,lost',
            'qty_total'     => 'required|integer|min:1',
            'qty_available' => 'required|integer|min:0|lte:qty_total',
            'location'      => 'nullable|string',
            'description'   => 'nullable|string',
        ]);

        Tool::create($validated);

        return redirect()->route('tools.index')->with('success', 'Tool created successfully.');
    }

    public function edit(Tool $tool)
    {
        $this->authorizeManager();
        return view('tools.edit', compact('tool'));
    }

    public function update(Request $request, Tool $tool)
    {
        $this->authorizeManager();
        $validated = $request->validate([
            'tool_code'     => 'nullable|string|unique:tools,tool_code,' . $tool->id,
            'name'          => 'required|string|max:255',
            'category'      => 'nullable|string',
            'brand'         => 'nullable|string',
            'condition'     => 'required|in:good,damaged,lost',
            'qty_total'     => 'required|integer|min:1',
            'qty_available' => 'required|integer|min:0|lte:qty_total',
            'location'      => 'nullable|string',
            'description'   => 'nullable|string',
        ]);

        $tool->update($validated);

        return redirect()->route('tools.index')->with('success', 'Tool updated successfully.');
    }

    public function destroy(Tool $tool)
    {
        $this->authorizeManager();
        $tool->delete();
        return redirect()->route('tools.index')->with('success', 'Tool deleted successfully.');
    }
}
