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

    public function destroy(Consumable $consumable)
    {
        $consumable->delete();
        return redirect()->route('consumables.index')->with('success', 'Consumable deleted successfully.');
    }
}
