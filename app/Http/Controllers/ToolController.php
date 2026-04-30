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

    public function destroy(Tool $tool)
    {
        $tool->delete();
        return redirect()->route('tools.index')->with('success', 'Tool deleted successfully.');
    }
}
