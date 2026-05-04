<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\DailyReportPhoto;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $reports = DailyReport::with('photos')
            ->where('user_id', auth()->id())
            ->orderBy('report_date', 'desc')
            ->get();

        $selectedReport = null;
        if ($request->id) {
            $selectedReport = DailyReport::with('photos')
                ->where('user_id', auth()->id())
                ->find($request->id);
        } elseif ($reports->isNotEmpty()) {
            $selectedReport = $reports->first();
        }

        return view('daily-reports.index', compact('reports', 'selectedReport'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id'          => 'nullable|integer',
            'report_date' => 'required|date',
            'title'       => 'nullable|string|max:255',
            'content'     => 'nullable|string',
        ]);

        if ($request->id) {
            $report = DailyReport::where('user_id', auth()->id())->findOrFail($request->id);
            $report->update([
                'title'       => $validated['title'] ?? 'Untitled Note',
                'content'     => $validated['content'],
                'report_date' => $validated['report_date'],
            ]);
        } else {
            $report = DailyReport::create([
                'user_id'     => auth()->id(),
                'report_date' => $validated['report_date'],
                'title'       => $validated['title'] ?? 'Untitled Note',
                'content'     => $validated['content'],
            ]);
        }

        return response()->json([
            'success' => true,
            'report'  => $report->load('photos')
        ]);
    }

    public function uploadPhoto(Request $request, DailyReport $dailyReport)
    {
        if ($dailyReport->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'photo' => 'required|image|max:10240', // 10MB
        ]);

        $path = $request->file('photo')->store('personal-notes', 'public');

        $photo = DailyReportPhoto::create([
            'daily_report_id' => $dailyReport->id,
            'file_path'       => $path,
        ]);

        return response()->json([
            'success' => true,
            'photo'   => [
                'id'  => $photo->id,
                'url' => Storage::url($path),
            ]
        ]);
    }

    public function deletePhoto(DailyReport $dailyReport, DailyReportPhoto $photo)
    {
        if ($dailyReport->user_id !== auth()->id() || $photo->daily_report_id !== $dailyReport->id) {
            abort(403);
        }

        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();

        return response()->json(['success' => true]);
    }

    public function destroy(DailyReport $dailyReport)
    {
        if ($dailyReport->user_id !== auth()->id()) {
            abort(403);
        }

        // Delete associated photos
        foreach ($dailyReport->photos as $photo) {
            Storage::disk('public')->delete($photo->file_path);
        }

        $dailyReport->delete();

        return redirect()->route('daily-reports.index')->with('success', 'Report deleted');
    }
}
