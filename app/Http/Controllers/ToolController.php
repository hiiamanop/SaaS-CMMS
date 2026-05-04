<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use Illuminate\Http\Request;

class ToolController extends Controller
{
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

    public function create()
    {
        return view('tools.create');
    }

    public function store(Request $request)
    {
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
        return view('tools.edit', compact('tool'));
    }

    public function update(Request $request, Tool $tool)
    {
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
        $tool->delete();
        return redirect()->route('tools.index')->with('success', 'Tool deleted successfully.');
    }
}
