<?php

namespace App\Http\Controllers;

use App\Models\Consumable;
use Illuminate\Http\Request;

class ConsumableController extends Controller
{
    private function authorizeManager(): void
    {
        if (!auth()->user()->isAdminOrSupervisor()) {
            abort(403, 'Unauthorized.');
        }
    }

    public function index(Request $request)
    {
        $query = Consumable::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('item_code', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filter === 'low_stock') {
            $query->whereRaw('qty_actual <= qty_minimum');
        }

        $items = $query->latest()->paginate(15)->withQueryString();
        $lowStockCount = Consumable::whereRaw('qty_actual <= qty_minimum')->count();

        return view('consumables.index', compact('items', 'lowStockCount'));
    }

    public function exportCsv(Request $request)
    {
        $filename = 'consumables_export_' . now()->format('Ymd_His') . '.csv';
        $query = Consumable::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('item_code', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->filter === 'low_stock') {
            $query->whereRaw('qty_actual <= qty_minimum');
        }

        $items = $query->orderBy('item_code')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($items) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Item Code', 'Name', 'Category', 'Unit', 'Qty Actual', 'Qty Minimum', 'Location', 'Supplier', 'Unit Price', 'Description'], ';');

            foreach ($items as $c) {
                fputcsv($handle, [
                    $c->item_code,
                    $c->name,
                    $c->category,
                    $c->unit,
                    $c->qty_actual,
                    $c->qty_minimum,
                    $c->location,
                    $c->supplier,
                    $c->unit_price,
                    $c->description,
                ], ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $this->authorizeManager();
        return view('consumables.create');
    }

    public function show(Consumable $consumable)
    {
        $consumable->load([
            'workOrderItems.workOrder',
            'workOrderItems.createdBy',
            'maintenanceRecordConsumables.maintenanceRecord.workOrder',
        ]);

        return view('consumables.show', compact('consumable'));
    }

    public function store(Request $request)
    {
        $this->authorizeManager();
        $validated = $request->validate([
            'item_code'   => 'nullable|string|unique:consumables',
            'name'        => 'required|string|max:255',
            'category'    => 'nullable|string',
            'unit'        => 'required|string',
            'qty_actual'  => 'required|integer|min:0',
            'qty_minimum' => 'required|integer|min:0',
            'unit_price'  => 'nullable|numeric|min:0',
            'supplier'    => 'nullable|string',
            'location'    => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        Consumable::create($validated);

        return redirect()->route('consumables.index')->with('success', 'Consumable created successfully.');
    }

    public function edit(Consumable $consumable)
    {
        $this->authorizeManager();
        return view('consumables.edit', compact('consumable'));
    }

    public function update(Request $request, Consumable $consumable)
    {
        $this->authorizeManager();
        $validated = $request->validate([
            'item_code'   => 'nullable|string|unique:consumables,item_code,' . $consumable->id,
            'name'        => 'required|string|max:255',
            'category'    => 'nullable|string',
            'unit'        => 'required|string',
            'qty_actual'  => 'required|integer|min:0',
            'qty_minimum' => 'required|integer|min:0',
            'unit_price'  => 'nullable|numeric|min:0',
            'supplier'    => 'nullable|string',
            'location'    => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $consumable->update($validated);

        return redirect()->route('consumables.index')->with('success', 'Consumable updated successfully.');
    }

    public function destroy(Consumable $consumable)
    {
        $this->authorizeManager();
        $consumable->delete();
        return redirect()->route('consumables.index')->with('success', 'Consumable deleted successfully.');
    }
}
