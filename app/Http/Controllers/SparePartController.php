<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class SparePartController extends Controller
{
    public function index(Request $request)
    {
        $query = SparePart::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('part_code', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->category) $query->where('category', $request->category);
        if ($request->filter === 'low_stock') $query->whereRaw('qty_actual <= qty_minimum');

        $parts = $query->latest()->paginate(15)->withQueryString();
        $categories = SparePart::distinct()->pluck('category');
        $lowStockCount = SparePart::whereRaw('qty_actual <= qty_minimum')->count();

        return view('spare-parts.index', compact('parts', 'categories', 'lowStockCount'));
    }

    public function create()
    {
        return view('spare-parts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_code' => 'required|string|unique:spare_parts',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string',
            'unit' => 'required|string',
            'qty_actual' => 'required|integer|min:0',
            'qty_minimum' => 'required|integer|min:0',
            'unit_price' => 'nullable|numeric',
            'supplier' => 'nullable|string',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $part = SparePart::create($validated);
        $this->checkAndNotifyLowStock($part);

        return redirect()->route('spare-parts.index')->with('success', 'Spare part created successfully.');
    }

    public function show(SparePart $sparePart)
    {
        return view('spare-parts.show', compact('sparePart'));
    }

    public function edit(SparePart $sparePart)
    {
        return view('spare-parts.edit', compact('sparePart'));
    }

    public function update(Request $request, SparePart $sparePart)
    {
        $validated = $request->validate([
            'part_code' => 'required|string|unique:spare_parts,part_code,'.$sparePart->id,
            'name' => 'required|string|max:255',
            'category' => 'nullable|string',
            'unit' => 'required|string',
            'qty_actual' => 'required|integer|min:0',
            'qty_minimum' => 'required|integer|min:0',
            'unit_price' => 'nullable|numeric',
            'supplier' => 'nullable|string',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $sparePart->update($validated);
        $this->checkAndNotifyLowStock($sparePart->fresh());

        return redirect()->route('spare-parts.index')->with('success', 'Spare part updated successfully.');
    }

    public function destroy(SparePart $sparePart)
    {
        $sparePart->delete();
        return redirect()->route('spare-parts.index')->with('success', 'Spare part deleted successfully.');
    }

    public function adjustStock(Request $request, SparePart $sparePart)
    {
        $request->validate([
            'type' => 'required|in:add,reduce',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($request->type === 'add') {
            $sparePart->increment('qty_actual', $request->quantity);
        } else {
            if ($request->quantity > $sparePart->qty_actual) {
                return back()->with('error', 'Cannot reduce more than current stock.');
            }
            $sparePart->decrement('qty_actual', $request->quantity);
        }

        $this->checkAndNotifyLowStock($sparePart->fresh());
        return back()->with('success', 'Stock adjusted successfully.');
    }

    private function checkAndNotifyLowStock(SparePart $part): void
    {
        if ($part->isLowStock()) {
            $adminsAndSupervisors = User::whereIn('role', ['admin', 'supervisor'])->get();
            foreach ($adminsAndSupervisors as $user) {
                $exists = Notification::where('user_id', $user->id)
                    ->where('type', 'low_stock')
                    ->where('is_read', false)
                    ->where('url', '/spare-parts/'.$part->id)
                    ->exists();

                if (!$exists) {
                    Notification::create([
                        'user_id' => $user->id,
                        'type' => 'low_stock',
                        'title' => 'Low Stock Alert',
                        'message' => "Spare part \"{$part->name}\" ({$part->part_code}) is low/out of stock. Current: {$part->qty_actual}, Minimum: {$part->qty_minimum}",
                        'url' => '/spare-parts/'.$part->id,
                    ]);
                }
            }
        }
    }
}
