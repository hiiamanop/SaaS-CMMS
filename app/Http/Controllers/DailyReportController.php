<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = DailyReport::where('user_id', auth()->id())
            ->orderBy('report_date', 'desc')
            ->get();

        $selectedReport = null;
        if ($request->id) {
            $selectedReport = DailyReport::where('user_id', auth()->id())->find($request->id);
        } elseif ($reports->isNotEmpty()) {
            $selectedReport = $reports->first();
        }

        return view('daily-reports.index', compact('reports', 'selectedReport'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_date' => 'required|date',
            'title'       => 'nullable|string|max:255',
            'content'     => 'nullable|string',
        ]);

        $report = DailyReport::updateOrCreate(
            [
                'user_id'     => auth()->id(),
                'report_date' => $validated['report_date'],
            ],
            [
                'title'   => $validated['title'] ?? 'Untitled Report',
                'content' => $validated['content'],
            ]
        );

        return response()->json([
            'success' => true,
            'report'  => $report
        ]);
    }

    public function destroy(DailyReport $dailyReport)
    {
        if ($dailyReport->user_id !== auth()->id()) {
            abort(403);
        }

        $dailyReport->delete();

        return redirect()->route('daily-reports.index')->with('success', 'Report deleted');
    }
}
