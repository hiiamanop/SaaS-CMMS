<?php

namespace App\Http\Controllers;

use App\Models\ChecksheetSession;

use App\Models\ChecksheetResult;
use App\Models\ChecksheetAbnormal;
use App\Models\MaintenanceSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChecksheetController extends Controller
{
    public function index(Request $request)
    {
        $scheduleFilter = $request->get('schedule_filter', 'all');
        $sessionMonth   = $request->get('month');
        $sessionYear    = $request->get('year'); // null = no year filter (show all years)

        $user = Auth::user();

        $schedules = MaintenanceSchedule::with(['technician', 'location'])
            ->where('status', 'active')
            ->when(!$user->isAdmin(), fn($q) => $q->where('location_id', $user->location_id))
            ->orderBy('category')
            ->orderBy('equipment_name')
            ->get();

        $currentWeekOfMonth = (int) now()->weekOfMonth;
        $currentMonth       = now()->month;
        $currentYear        = now()->year;

        $today = now()->startOfDay();

        // Load sessions per schedule — filter by period, then sort by closest due date
        foreach ($schedules as $schedule) {
            $sessQuery = $schedule->checksheetSessions();

            switch ($scheduleFilter) {
                case 'today':
                case 'week':
                    if ($schedule->frequency === 'weekly') {
                        $sessQuery->where('year', $currentYear)
                                  ->where('month', $currentMonth)
                                  ->where('week_number', $currentWeekOfMonth);
                    } else {
                        $sessQuery->where('year', $currentYear)
                                  ->where('month', $currentMonth);
                    }
                    break;
                case 'month':
                    $sessQuery->where('year', $currentYear)->where('month', $currentMonth);
                    break;
                case 'overdue':
                    // Only draft sessions — due date check happens after fetch
                    $sessQuery->where('status', '!=', 'submitted');
                    break;
                default:
                    if ($sessionMonth) $sessQuery->where('month', $sessionMonth);
                    if ($sessionYear)  $sessQuery->where('year', $sessionYear);
                    break;
            }

            $freq     = $schedule->frequency;
            $fetched  = $sessQuery->get();

            if ($scheduleFilter === 'overdue') {
                // Keep only sessions whose due date has fully passed
                $sessions = $fetched
                    ->filter(fn($s) => $this->sessionDueDate($s, $freq)->isPast())
                    ->sortBy(fn($s) => $this->sessionDueDate($s, $freq)->timestamp) // most overdue first
                    ->values();
            } else {
                // Sort by closest due date to today
                $sessions = $fetched->sortBy(function ($s) use ($today, $freq) {
                    return abs($today->diffInDays($this->sessionDueDate($s, $freq), false));
                })->values();
            }

            $schedule->setRelation('checksheetSessions', $sessions);
        }

        // When filter is active, hide schedules that have no matching sessions
        if ($scheduleFilter !== 'all') {
            $schedules = $schedules->filter(fn($s) => $s->checksheetSessions->isNotEmpty());
        }

        $currentYear  = now()->year;
        $currentMonth = now()->month;

        return view('checksheet.index', compact(
            'schedules', 'currentYear', 'currentMonth',
            'scheduleFilter', 'sessionMonth', 'sessionYear'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'maintenance_schedule_id' => 'required|exists:maintenance_schedules,id',
            'year'                    => 'required|integer',
            'week_number'             => 'nullable|integer',
            'month'                   => 'nullable|integer',
            'quarter'                 => 'nullable|integer',
            'semester'                => 'nullable|integer',
        ]);

        $schedule = MaintenanceSchedule::findOrFail($request->maintenance_schedule_id);
        $period   = $this->buildPeriodLabel($schedule->frequency, $request->all());

        $session = ChecksheetSession::create([
            'maintenance_schedule_id' => $schedule->id,
            'plts_location'           => $schedule->location?->name ?? $schedule->equipment_name,
            'equipment_location'      => $schedule->equipment_name,
            'period_label'            => $period,
            'year'                    => $request->year,
            'week_number'             => $request->week_number,
            'month'                   => $request->month,
            'quarter'                 => $request->quarter,
            'semester'                => $request->semester,
            'status'                  => 'draft',
        ]);

        return redirect()->route('checksheet.fill', $session);
    }

    public function fill(ChecksheetSession $session)
    {
        $session->load(['schedule', 'results', 'abnormals']);

        $items   = $session->schedule->item_pekerjaan ?? [];
        $results = $session->results->keyBy('item_name');
        $total   = is_array($items) ? count($items) : 0;
        $filled  = $session->results->whereNotNull('result')->count();

        return view('checksheet.fill', compact('session', 'items', 'results', 'total', 'filled'));
    }

    public function autosave(Request $request, ChecksheetSession $session)
    {
        if ($session->status === 'submitted') {
            return response()->json(['ok' => false, 'message' => 'Already submitted']);
        }

        $items = $request->get('items', []);
        foreach ($items as $itemName => $data) {
            $result = ChecksheetResult::firstOrNew([
                'session_id' => $session->id,
                'item_name'  => $itemName,
            ]);
            $result->result = $data['result'] ?? null;
            $result->notes  = $data['notes'] ?? null;
            $result->save();
        }

        if ($request->has('abnormals')) {
            $session->abnormals()->delete();
            foreach ($request->get('abnormals', []) as $ab) {
                if (!empty($ab['abnormal_description'])) {
                    ChecksheetAbnormal::create([
                        'session_id'           => $session->id,
                        'tanggal'              => $ab['tanggal'] ?? null,
                        'abnormal_description' => $ab['abnormal_description'],
                        'penanganan'           => $ab['penanganan'] ?? null,
                        'tgl_selesai'          => $ab['tgl_selesai'] ?? null,
                        'pic'                  => $ab['pic'] ?? null,
                    ]);
                }
            }
        }

        return response()->json(['ok' => true, 'saved_at' => now()->format('H:i:s')]);
    }

    public function uploadPhoto(Request $request, ChecksheetSession $session, $templateId)
    {
        $request->validate([
            'photo' => 'required|file|mimes:jpg,jpeg,png,heic|max:5120',
        ]);

        $path = $request->file('photo')->store(
            "checksheet-photos/{$session->id}/{$templateId}",
            'public'
        );

        $result = ChecksheetResult::firstOrNew([
            'session_id' => $session->id,
            'item_name'  => $templateId, // using templateId param as itemName
        ]);
        $photos   = $result->photos ?? [];
        $photos[] = $path;
        $result->photos = $photos;
        $result->save();

        return response()->json(['path' => $path, 'url' => Storage::url($path)]);
    }

    public function submit(Request $request, ChecksheetSession $session)
    {
        $request->validate([
            'signed_by_teknisi'   => 'required|string',
            'signed_date_teknisi' => 'required|date',
            'signed_by_spv'       => 'nullable|string',
            'signed_date_spv'     => 'nullable|date',
            'signed_by_pm'        => 'nullable|string',
            'signed_date_pm'      => 'nullable|date',
        ]);

        $items       = $session->schedule->item_pekerjaan ?? [];
        $totalCount  = is_array($items) ? count($items) : 0;
        $filledCount = $session->results()->whereNotNull('result')->count();

        if ($filledCount < $totalCount) {
            return back()->withErrors(['submit' => 'Semua item harus diisi sebelum submit.']);
        }

        $session->update([
            'status'              => 'submitted',
            'submitted_at'        => now(),
            'submitted_by'        => Auth::id(),
            'signed_by_teknisi'   => $request->signed_by_teknisi,
            'signed_date_teknisi' => $request->signed_date_teknisi,
            'signed_by_spv'       => $request->signed_by_spv,
            'signed_date_spv'     => $request->signed_date_spv,
            'signed_by_pm'        => $request->signed_by_pm,
            'signed_date_pm'      => $request->signed_date_pm,
        ]);

        return redirect()->route('checksheet.show', $session)
            ->with('success', 'Checksheet berhasil disubmit.');
    }

    public function show(ChecksheetSession $session)
    {
        $session->load(['schedule', 'results', 'abnormals', 'submittedBy']);
        $items   = $session->schedule->item_pekerjaan ?? [];
        $results = $session->results->keyBy('item_name');

        return view('checksheet.show', compact('session', 'items', 'results'));
    }

    public function exportPdf(ChecksheetSession $session)
    {
        $session->load(['schedule', 'results', 'abnormals', 'submittedBy']);
        $items   = $session->schedule->item_pekerjaan ?? [];
        $results = $session->results->keyBy('item_name');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('checksheet.pdf', compact('session', 'items', 'results'))
            ->setPaper('a4', 'landscape');

        $filename = implode('_', [
            str_replace(' ', '-', $session->schedule->equipment_name),
            str_replace(' ', '-', $session->plts_location),
            $session->period_label,
            $session->year,
        ]) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Calculate the due date (end of period) for a session.
     */
    public function sessionDueDate(\App\Models\ChecksheetSession $s, string $frequency): Carbon
    {
        $year = $s->year ?? now()->year;
        return match($frequency) {
            'weekly'    => Carbon::createFromDate($year, $s->month ?? 1, 1)->addWeeks($s->week_number ?? 1)->subDay()->endOfDay(),
            'monthly'   => Carbon::createFromDate($year, $s->month ?? 1, 1)->endOfMonth(),
            'triwulan'  => match((int)($s->quarter ?? 1)) {
                1 => Carbon::createFromDate($year, 3, 31)->endOfDay(),
                2 => Carbon::createFromDate($year, 6, 30)->endOfDay(),
                3 => Carbon::createFromDate($year, 9, 30)->endOfDay(),
                4 => Carbon::createFromDate($year, 12, 31)->endOfDay(),
                default => Carbon::createFromDate($year, 12, 31)->endOfDay(),
            },
            'quarterly' => ($s->semester == 1)
                ? Carbon::createFromDate($year, 6, 30)->endOfDay()
                : Carbon::createFromDate($year, 12, 31)->endOfDay(),
            'annually'  => Carbon::createFromDate($year, 12, 31)->endOfDay(),
            default     => Carbon::createFromDate($year, 12, 31)->endOfDay(),
        };
    }

    private function buildPeriodLabel(string $frequency, array $data): string
    {
        return match($frequency) {
            'weekly'    => 'Week ' . ($data['week_number'] ?? 1) . ' - ' . Carbon::createFromDate($data['year'], $data['month'] ?? 1, 1)->format('M Y'),
            'monthly'   => Carbon::createFromDate($data['year'], $data['month'] ?? 1, 1)->format('F Y'),
            'triwulan'  => 'Kuartal ' . ($data['quarter'] ?? 1) . ' ' . $data['year'],
            'quarterly' => 'Semester ' . ($data['semester'] ?? 1) . ' ' . $data['year'],
            'annually'  => (string)$data['year'],
            default     => (string)$data['year'],
        };
    }
}
