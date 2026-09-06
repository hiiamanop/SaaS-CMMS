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

    public function create()
    {
        $this->authorizeManager();
        return view('consumables.create');
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
