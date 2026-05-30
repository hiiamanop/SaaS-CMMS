<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('asset_code', 'like', '%' . $request->search . '%')
                    ->orWhere('serial_number', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->category)
            $query->where('category', $request->category);
        if ($request->status)
            $query->where('status', $request->status);
        if ($request->location)
            $query->where('location', 'like', '%' . $request->location . '%');

        $assets = $query->orderBy('location')->orderBy('name')->paginate(15)->withQueryString();
        $categories = Asset::distinct()->pluck('category');
        $locations = Asset::distinct()->pluck('location');

        return view('assets.index', compact('assets', 'categories', 'locations'));
    }

    public function create()
    {
        $pltsList = \App\Models\Location::where('is_active', true)->orderBy('name')->get();
        return view('assets.create', compact('pltsList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_code' => 'nullable|string|unique:assets',
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,replaced,retired',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'warranty_expiry' => 'nullable|date',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'transformer_block' => 'nullable|string|max:20',
            'string_number' => 'nullable|integer|min:1|max:999',
            'module_slot' => 'nullable|integer|min:1|max:999',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('assets', 'public');
        }

        Asset::create($validated);
        return redirect()->route('assets.index')->with('success', 'Asset created successfully.');
    }

    public function show(Asset $asset)
    {
        $asset->load(['workOrders.assignedTo', 'maintenanceRecords.technician']);
        $openWorkOrders = $asset->workOrders()->whereNotIn('status', ['closed'])->count();
        $totalMaintenance = $asset->maintenanceRecords()->count();
        $totalDowntime = $asset->maintenanceRecords()->sum('shutdown_minutes');

        return view('assets.show', compact('asset', 'openWorkOrders', 'totalMaintenance', 'totalDowntime'));
    }

    public function edit(Asset $asset)
    {
        $pltsList = \App\Models\Location::where('is_active', true)->orderBy('name')->get();
        return view('assets.edit', compact('asset', 'pltsList'));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'asset_code' => 'nullable|string|unique:assets,asset_code,' . $asset->id,
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,replaced,retired',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'warranty_expiry' => 'nullable|date',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'transformer_block' => 'nullable|string|max:20',
            'string_number' => 'nullable|integer|min:1|max:999',
            'module_slot' => 'nullable|integer|min:1|max:999',
        ]);

        if ($request->hasFile('photo')) {
            if ($asset->photo)
                Storage::disk('public')->delete($asset->photo);
            $validated['photo'] = $request->file('photo')->store('assets', 'public');
        }

        $asset->update($validated);
        return redirect()->route('assets.show', $asset)->with('success', 'Asset updated successfully.');
    }

    public function destroy(Asset $asset)
    {
        if ($asset->photo)
            Storage::disk('public')->delete($asset->photo);
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Asset deleted successfully.');
    }

    public function updatePosition(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|integer|exists:assets,id',
            'row'      => 'required|integer|min:1',
            'col'      => 'required|integer|min:1',
        ]);

        Asset::where('id', $request->asset_id)->update([
            'visual_row' => $request->row,
            'visual_col' => $request->col,
        ]);

        return response()->json(['ok' => true]);
    }

    public function swapPosition(Request $request)
    {
        $request->validate([
            'asset_a' => 'required|integer|exists:assets,id',
            'asset_b' => 'nullable|integer|exists:assets,id',
            'row_b'   => 'required|integer|min:1',
            'col_b'   => 'required|integer|min:1',
        ]);

        $assetA = Asset::findOrFail($request->asset_a);
        $rowA   = $assetA->visual_row;
        $colA   = $assetA->visual_col;

        if ($request->asset_b) {
            $assetB = Asset::findOrFail($request->asset_b);
            $assetB->update(['visual_row' => $rowA, 'visual_col' => $colA]);
        }

        $assetA->update(['visual_row' => $request->row_b, 'visual_col' => $request->col_b]);

        return response()->json(['ok' => true]);
    }

    public function byLocation(Request $request)
    {
        $assets = Asset::where('location_id', $request->location_id)
            ->orderBy('name')
            ->get(['id', 'name', 'asset_code', 'status', 'category', 'visual_row', 'visual_col', 'transformer_block']);

        return response()->json($assets);
    }

    public function quickSavePv(Request $request)
    {
        $request->validate([
            'asset_id'          => 'required|integer|exists:assets,id',
            'transformer_block' => 'required|string|max:100',
            'visual_row'        => 'required|integer|min:1',
            'visual_col'        => 'required|integer|min:1',
        ]);

        Asset::where('id', $request->asset_id)->update([
            'transformer_block' => $request->transformer_block,
            'visual_row'        => $request->visual_row,
            'visual_col'        => $request->visual_col,
        ]);

        return response()->json(['ok' => true]);
    }

    public function destroyPv(Asset $asset)
    {
        // Hapus dari grid (bersihkan posisi visual), tidak hapus asset
        $asset->update(['visual_row' => null, 'visual_col' => null, 'transformer_block' => null]);
        return response()->json(['ok' => true]);
    }
}
