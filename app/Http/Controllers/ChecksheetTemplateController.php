<?php

namespace App\Http\Controllers;

use App\Models\ChecksheetTemplate;
use App\Models\MaintenanceSchedule;
use Illuminate\Http\Request;

class ChecksheetTemplateController extends Controller
{
    public function index(Request $request)
    {
        $schedules = MaintenanceSchedule::with(['asset', 'technician', 'checklistTemplates'])
            ->where('status', 'active')
            ->orderBy('category')
            ->orderBy('equipment_name')
            ->get();

        $activeScheduleId = $request->get('schedule_id', $schedules->first()?->id);
        $activeSchedule   = $schedules->firstWhere('id', $activeScheduleId);

        return view('checksheet.templates.index', compact('schedules', 'activeSchedule'));
    }

    public function storeItem(Request $request, MaintenanceSchedule $schedule)
    {
        $data = $request->validate([
            'lokasi_inspeksi'   => 'required|string|max:200',
            'item_inspeksi'     => 'required|string|max:300',
            'metode_inspeksi'   => 'nullable|string|max:200',
            'standar_ketentuan' => 'nullable|string|max:300',
        ]);

        $maxOrder = ChecksheetTemplate::where('maintenance_schedule_id', $schedule->id)
            ->where('lokasi_inspeksi', $data['lokasi_inspeksi'])
            ->max('order') ?? 0;

        ChecksheetTemplate::create([
            'maintenance_schedule_id' => $schedule->id,
            'lokasi_inspeksi'         => $data['lokasi_inspeksi'],
            'item_inspeksi'           => $data['item_inspeksi'],
            'metode_inspeksi'         => $data['metode_inspeksi'] ?? null,
            'standar_ketentuan'       => $data['standar_ketentuan'] ?? null,
            'order'                   => $maxOrder + 1,
        ]);

        return redirect()->route('checksheet.templates.index', ['schedule_id' => $schedule->id])
            ->with('success', 'Item berhasil ditambahkan.');
    }

    public function updateItem(Request $request, ChecksheetTemplate $item)
    {
        $data = $request->validate([
            'lokasi_inspeksi'   => 'required|string|max:200',
            'item_inspeksi'     => 'required|string|max:300',
            'metode_inspeksi'   => 'nullable|string|max:200',
            'standar_ketentuan' => 'nullable|string|max:300',
        ]);

        $item->update($data);

        return redirect()->route('checksheet.templates.index', ['schedule_id' => $item->maintenance_schedule_id])
            ->with('success', 'Item berhasil diperbarui.');
    }

    public function destroyItem(ChecksheetTemplate $item)
    {
        $scheduleId = $item->maintenance_schedule_id;
        $item->delete();

        return redirect()->route('checksheet.templates.index', ['schedule_id' => $scheduleId])
            ->with('success', 'Item berhasil dihapus.');
    }
}
