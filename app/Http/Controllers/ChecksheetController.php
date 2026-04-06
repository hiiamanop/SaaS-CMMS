<?php

namespace App\Http\Controllers;

use App\Models\ChecksheetSession;
use App\Models\ChecksheetTemplate;
use App\Models\ChecksheetType;
use App\Models\ChecksheetResult;
use App\Models\ChecksheetAbnormal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChecksheetController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $currentWeek = $now->weekOfMonth;
        $currentMonth = $now->month;
        $currentSemester = $now->month <= 6 ? 1 : 2;
        $currentYear = $now->year;

        $pltsLocations = ChecksheetSession::distinct()->pluck('plts_location')->toArray();

        // Load existing sessions for today's context
        $sessions = ChecksheetSession::with('type')
            ->where('year', $currentYear)
            ->get();

        // Get types
        $types = ChecksheetType::all();

        // Build pending cards grouped by type
        $pending = [];
        foreach ($types as $type) {
            $typeSessions = $sessions->where('checksheet_type_id', $type->id);
            foreach ($typeSessions as $session) {
                $pending[$type->frequency][] = $session;
            }
        }

        return view('checksheet.index', compact('pending', 'types', 'currentYear', 'currentWeek', 'currentMonth', 'currentSemester'));
    }

    public function create(Request $request)
    {
        $typeId = $request->get('type_id');
        $type = ChecksheetType::findOrFail($typeId);

        return view('checksheet.create', compact('type'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'checksheet_type_id' => 'required|exists:checksheet_types,id',
            'plts_location' => 'required|string',
            'equipment_location' => 'nullable|string',
            'year' => 'required|integer',
            'week_number' => 'nullable|integer',
            'month' => 'nullable|integer',
            'semester' => 'nullable|integer',
        ]);

        $type = ChecksheetType::findOrFail($request->checksheet_type_id);

        $period = $this->buildPeriodLabel($type->frequency, $request->all());

        $session = ChecksheetSession::create([
            'checksheet_type_id' => $request->checksheet_type_id,
            'plts_location' => $request->plts_location,
            'equipment_location' => $request->equipment_location,
            'period_label' => $period,
            'year' => $request->year,
            'week_number' => $request->week_number,
            'month' => $request->month,
            'semester' => $request->semester,
            'status' => 'draft',
        ]);

        return redirect()->route('checksheet.fill', $session);
    }

    public function fill(ChecksheetSession $session)
    {
        $session->load(['type.templates', 'results.template', 'abnormals']);

        $templates = $session->type->templates()->orderBy('order')->get();
        $results = $session->results->keyBy('template_id');
        $total = $templates->count();
        $filled = $session->results->whereNotNull('result')->count();

        return view('checksheet.fill', compact('session', 'templates', 'results', 'total', 'filled'));
    }

    public function autosave(Request $request, ChecksheetSession $session)
    {
        if ($session->status === 'submitted') {
            return response()->json(['ok' => false, 'message' => 'Already submitted']);
        }

        $items = $request->get('items', []);
        foreach ($items as $templateId => $data) {
            $result = ChecksheetResult::firstOrNew([
                'session_id' => $session->id,
                'template_id' => $templateId,
            ]);
            $result->result = $data['result'] ?? null;
            $result->notes = $data['notes'] ?? null;
            $result->save();
        }

        // Handle abnormals
        if ($request->has('abnormals')) {
            $session->abnormals()->delete();
            foreach ($request->get('abnormals', []) as $ab) {
                if (!empty($ab['abnormal_description'])) {
                    ChecksheetAbnormal::create([
                        'session_id' => $session->id,
                        'tanggal' => $ab['tanggal'] ?? null,
                        'abnormal_description' => $ab['abnormal_description'],
                        'penanganan' => $ab['penanganan'] ?? null,
                        'tgl_selesai' => $ab['tgl_selesai'] ?? null,
                        'pic' => $ab['pic'] ?? null,
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
            'template_id' => $templateId,
        ]);
        $photos = $result->photos ?? [];
        $photos[] = $path;
        $result->photos = $photos;
        $result->save();

        return response()->json(['path' => $path, 'url' => Storage::url($path)]);
    }

    public function submit(Request $request, ChecksheetSession $session)
    {
        $request->validate([
            'signed_by_teknisi' => 'required|string',
            'signed_date_teknisi' => 'required|date',
            'signed_by_spv' => 'nullable|string',
            'signed_date_spv' => 'nullable|date',
            'signed_by_pm' => 'nullable|string',
            'signed_date_pm' => 'nullable|date',
        ]);

        // Validate all items filled
        $templates = $session->type->templates();
        $totalCount = $templates->count();
        $filledCount = $session->results()->whereNotNull('result')->count();

        if ($filledCount < $totalCount) {
            return back()->withErrors(['submit' => 'Semua item harus diisi sebelum submit.']);
        }

        // Check photo requirements for X items on non-weekly
        if ($session->type->frequency !== 'weekly') {
            $xWithoutPhoto = $session->results()
                ->where('result', 'X')
                ->whereNull('photos')
                ->count();
            if ($xWithoutPhoto > 0) {
                return back()->withErrors(['submit' => 'Lengkapi foto untuk semua item anomali.']);
            }
        }

        // Save signatures
        $session->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => Auth::id(),
            'signed_by_teknisi' => $request->signed_by_teknisi,
            'signed_date_teknisi' => $request->signed_date_teknisi,
            'signed_by_spv' => $request->signed_by_spv,
            'signed_date_spv' => $request->signed_date_spv,
            'signed_by_pm' => $request->signed_by_pm,
            'signed_date_pm' => $request->signed_date_pm,
        ]);

        return redirect()->route('checksheet.show', $session)
            ->with('success', 'Checksheet berhasil disubmit.');
    }

    public function show(ChecksheetSession $session)
    {
        $session->load(['type.templates', 'results.template', 'abnormals', 'submittedBy']);
        $templates = $session->type->templates()->orderBy('order')->get();
        $results = $session->results->keyBy('template_id');

        return view('checksheet.show', compact('session', 'templates', 'results'));
    }

    public function exportPdf(ChecksheetSession $session)
    {
        $session->load(['type.templates', 'results.template', 'abnormals', 'submittedBy']);
        $templates = $session->type->templates()->orderBy('order')->get();
        $results = $session->results->keyBy('template_id');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('checksheet.pdf', compact('session', 'templates', 'results'))
            ->setPaper('a4', 'landscape');

        $filename = implode('_', [
            strtoupper($session->type->name),
            str_replace(' ', '-', $session->plts_location),
            $session->period_label,
            $session->year,
        ]) . '.pdf';

        return $pdf->download($filename);
    }

    private function buildPeriodLabel(string $frequency, array $data): string
    {
        return match($frequency) {
            'weekly' => 'Week ' . ($data['week_number'] ?? 1) . ' - ' . Carbon::createFromDate($data['year'], $data['month'] ?? 1, 1)->format('M Y'),
            'monthly' => Carbon::createFromDate($data['year'], $data['month'] ?? 1, 1)->format('F Y'),
            'semester' => 'Semester ' . ($data['semester'] ?? 1) . ' ' . $data['year'],
            'yearly' => (string)$data['year'],
            default => (string)$data['year'],
        };
    }
}
