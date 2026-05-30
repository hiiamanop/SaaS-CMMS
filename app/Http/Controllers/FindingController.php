<?php

namespace App\Http\Controllers;

use App\Models\Finding;
use App\Models\Location;
use Illuminate\Http\Request;

class FindingController extends Controller
{
    public function index(Request $request)
    {
        $query = Finding::with(['session.schedule', 'location', 'reportedBy'])->latest();

        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('severity'))    $query->where('severity', $request->severity);
        if ($request->filled('source_type')) $query->where('source_type', $request->source_type);
        if ($request->filled('search'))      $query->where('title', 'like', '%' . $request->search . '%');

        $findings  = $query->paginate(20)->withQueryString();
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return view('findings.index', compact('findings', 'locations'));
    }

    public function create()
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        return view('findings.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'status'        => 'required|in:open,in_progress,resolved,closed',
            'location_id'   => 'nullable|exists:locations,id',
            'found_date'    => 'nullable|date',
            'resolved_date' => 'nullable|date',
            'action_taken'  => 'nullable|string',
        ]);

        $extra = [
            'source_type'  => 'manual',
            'reported_by'  => auth()->id(),
            'finding_time' => now(),
        ];

        if (in_array($validated['status'], ['resolved', 'closed'])) {
            $extra['close_time'] = now();
        }

        Finding::create(array_merge($validated, $extra));

        return redirect()->route('findings.index')->with('success', 'Finding berhasil ditambahkan.');
    }

    public function show(Finding $finding)
    {
        $finding->load(['session.schedule.location', 'location', 'reportedBy']);
        return view('findings.show', compact('finding'));
    }

    public function edit(Finding $finding)
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        return view('findings.edit', compact('finding', 'locations'));
    }

    public function update(Request $request, Finding $finding)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'status'        => 'required|in:open,in_progress,resolved,closed',
            'location_id'   => 'nullable|exists:locations,id',
            'found_date'    => 'nullable|date',
            'resolved_date' => 'nullable|date',
            'action_taken'  => 'nullable|string',
        ]);

        // Set close_time otomatis saat status berubah ke resolved/closed
        if (in_array($validated['status'], ['resolved', 'closed']) && !$finding->close_time) {
            $validated['close_time'] = now();
        }

        $finding->update($validated);

        return redirect()->route('findings.show', $finding)->with('success', 'Finding berhasil diperbarui.');
    }

    public function destroy(Finding $finding)
    {
        $finding->delete();
        return redirect()->route('findings.index')->with('success', 'Finding berhasil dihapus.');
    }
}
