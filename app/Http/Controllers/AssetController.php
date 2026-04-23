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
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('asset_code', 'like', '%'.$request->search.'%')
                  ->orWhere('serial_number', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->category) $query->where('category', $request->category);
        if ($request->status) $query->where('status', $request->status);
        if ($request->location) $query->where('location', 'like', '%'.$request->location.'%');

        $assets = $query->latest()->paginate(15)->withQueryString();
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
            'asset_code' => 'required|string|unique:assets',
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,under_maintenance,retired',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'warranty_expiry' => 'nullable|date',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
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
            'asset_code' => 'required|string|unique:assets,asset_code,'.$asset->id,
            'name' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'category' => 'required|string|max:100',
            'location' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,under_maintenance,retired',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'warranty_expiry' => 'nullable|date',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            if ($asset->photo) Storage::disk('public')->delete($asset->photo);
            $validated['photo'] = $request->file('photo')->store('assets', 'public');
        }

        $asset->update($validated);
        return redirect()->route('assets.show', $asset)->with('success', 'Asset updated successfully.');
    }

    public function destroy(Asset $asset)
    {
        if ($asset->photo) Storage::disk('public')->delete($asset->photo);
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Asset deleted successfully.');
    }
}
