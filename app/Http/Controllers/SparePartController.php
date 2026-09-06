<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use App\Models\Notification;
use App\Models\User;
use App\Services\StockService;
use App\Exceptions\OutOfStockException;
use Illuminate\Http\Request;

class SparePartController extends Controller
{
    private function authorizeManager(): void
    {
        if (!auth()->user()->isAdminOrSupervisor()) {
            abort(403, 'Unauthorized.');
        }
    }

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
        if ($request->type) $query->where('category', $request->type);
        if ($request->filter === 'low_stock') $query->whereRaw('qty_actual <= qty_minimum');

        $parts = $query->latest()->paginate(15)->withQueryString();
        $categories = SparePart::distinct()->pluck('category');
        $lowStockCount = SparePart::whereRaw('qty_actual <= qty_minimum')->count();

        return view('spare-parts.index', compact('parts', 'categories', 'lowStockCount'));
    }

    public function exportCsv(Request $request)
    {
        $filename = 'spare_parts_export_' . now()->format('Ymd_His') . '.csv';
        $query = SparePart::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('part_code', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->category) $query->where('category', $request->category);
        if ($request->filter === 'low_stock') $query->whereRaw('qty_actual <= qty_minimum');

        $parts = $query->orderBy('part_code')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($parts) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Code', 'Name', 'Category', 'Unit', 'Qty Actual', 'Qty Minimum', 'Unit Price', 'Supplier', 'Location', 'Description'], ';');

            foreach ($parts as $p) {
                fputcsv($handle, [
                    $p->part_code,
                    $p->name,
                    $p->category,
                    $p->unit,
                    $p->qty_actual,
                    $p->qty_minimum,
                    $p->unit_price,
                    $p->supplier,
                    $p->location,
                    $p->description,
                ], ';');
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        $this->authorizeManager();
        return view('spare-parts.create');
    }

    public function store(Request $request)
    {
        $this->authorizeManager();
        $validated = $request->validate([
            'part_code' => 'nullable|string|unique:spare_parts',
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
        $this->authorizeManager();
        return view('spare-parts.edit', compact('sparePart'));
    }

    public function update(Request $request, SparePart $sparePart)
    {
        $this->authorizeManager();
        $validated = $request->validate([
            'part_code' => 'nullable|string|unique:spare_parts,part_code,'.$sparePart->id,
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

        $oldQty = (int) $sparePart->qty_actual;
        $sparePart->update($validated);
        $newQty = (int) $sparePart->fresh()->qty_actual;

        if ($newQty !== $oldQty) {
            $diff = abs($newQty - $oldQty);
            $mutationType = $newQty > $oldQty ? 'add' : 'deduct';
            \App\Models\StockMovement::create([
                'spare_part_id' => $sparePart->id,
                'mutation_type' => $mutationType,
                'qty' => $diff,
                'reason' => 'manual_edit',
                'created_by_user_id' => auth()->id() ?? 1,
            ]);
        }

        $this->checkAndNotifyLowStock($sparePart->fresh());

        return redirect()->route('spare-parts.index')->with('success', 'Spare part updated successfully.');
    }

    public function destroy(SparePart $sparePart)
    {
        $this->authorizeManager();
        $sparePart->delete();
        return redirect()->route('spare-parts.index')->with('success', 'Spare part deleted successfully.');
    }

    public function adjustStock(Request $request, SparePart $sparePart)
    {
        $this->authorizeManager();
        $request->validate([
            'type' => 'required|in:add,reduce',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            if ($request->type === 'add') {
                StockService::add($sparePart, $request->quantity, 'stock_adjustment', auth()->id());
            } else {
                StockService::deduct($sparePart, $request->quantity, 'stock_adjustment', auth()->id());
            }
        } catch (OutOfStockException $e) {
            return back()->with('error', $e->getMessage());
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
