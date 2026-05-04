<?php

namespace App\Http\Controllers;

use App\Models\Consumable;
use Illuminate\Http\Request;

class ConsumableController extends Controller
{
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
        return view('consumables.create');
    }

    public function store(Request $request)
    {
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
        return view('consumables.edit', compact('consumable'));
    }

    public function update(Request $request, Consumable $consumable)
    {
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
        $consumable->delete();
        return redirect()->route('consumables.index')->with('success', 'Consumable deleted successfully.');
    }
}
