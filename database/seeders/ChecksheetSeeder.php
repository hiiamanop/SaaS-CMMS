<?php

namespace Database\Seeders;

use App\Models\ChecksheetAbnormal;
use App\Models\ChecksheetResult;
use App\Models\ChecksheetSession;
use App\Models\ChecksheetTemplate;
use App\Models\ChecksheetType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ChecksheetSeeder extends Seeder
{
    public function run(): void
    {
        // Create types
        $weekly = ChecksheetType::create(['name' => 'Mingguan', 'frequency' => 'weekly']);
        $monthly = ChecksheetType::create(['name' => 'Bulanan', 'frequency' => 'monthly']);
        $semester = ChecksheetType::create(['name' => 'Semesteran', 'frequency' => 'semester']);
        $annual = ChecksheetType::create(['name' => 'Tahunan', 'frequency' => 'yearly']);

        // WEEKLY templates
        $weeklyItems = [
            ['lokasi' => 'PV Module', 'item' => 'Pengecekan kondisi PV Module', 'metode' => 'Visual Check', 'standar' => 'Bersih, Tidak Kotor'],
            ['lokasi' => 'PV Module', 'item' => 'Pengecekan mounting PV Module', 'metode' => 'Visual Check', 'standar' => 'Tidak Bergeser, Tidak Turun'],
            ['lokasi' => 'PV Module', 'item' => 'Pengecekan kondisi skun grounding antar PV', 'metode' => 'Visual Check', 'standar' => 'Tidak Terlepas'],
            ['lokasi' => 'PV Module', 'item' => 'Pengecekan bracing, end clamp, dan midclamp', 'metode' => 'Visual Check', 'standar' => 'Tidak Terlepas'],
            ['lokasi' => 'PV Module', 'item' => 'Pengecekan kabel DC String', 'metode' => 'Visual Check', 'standar' => 'Tidak Terkelupas/Robek, Rapih'],
            ['lokasi' => 'Inverter', 'item' => 'Kebersihan Inverter', 'metode' => 'Visual Check & Pembersihan', 'standar' => 'Bersih, Tidak Kotor'],
            ['lokasi' => 'Inverter', 'item' => 'MC4 Connector', 'metode' => 'Visual Check & Thermal Test', 'standar' => 'Tidak Rusak/Terbakar, Tidak Kendur'],
            ['lokasi' => 'Inverter', 'item' => 'Thermal check MC4', 'metode' => 'Thermal Test/Measurement', 'standar' => '≤60°C Normal, 60-70°C Warning, >70°C Critical'],
            ['lokasi' => 'Inverter', 'item' => 'Pengecekan Torque Kabel LV AC', 'metode' => 'Visual Check', 'standar' => 'Sesuai Standar'],
            ['lokasi' => 'Inverter', 'item' => 'Historikal Alarm', 'metode' => 'Pengecekan Software', 'standar' => 'Tidak Ada Alarm'],
            ['lokasi' => 'Panel LV', 'item' => 'Pengecekan kondisi SPD', 'metode' => 'Visual Check', 'standar' => 'Koneksi Grounding, Indikator Status, Tidak Rusak'],
            ['lokasi' => 'Panel LV', 'item' => 'Pengecekan kondisi visual MCCB Inverter', 'metode' => 'Visual Check', 'standar' => 'Kondisi Fisik, Indikator Trip'],
            ['lokasi' => 'Panel LV', 'item' => 'Pengecekan CB Control', 'metode' => 'Visual Check', 'standar' => 'Tidak Terlepas, Terbakar'],
            ['lokasi' => 'Panel LV', 'item' => 'Visual Check UPS', 'metode' => 'Visual Check', 'standar' => 'Tidak Ada Alarm'],
        ];

        foreach ($weeklyItems as $i => $item) {
            ChecksheetTemplate::create([
                'checksheet_type_id' => $weekly->id,
                'lokasi_inspeksi' => $item['lokasi'],
                'item_inspeksi' => $item['item'],
                'metode_inspeksi' => $item['metode'],
                'standar_ketentuan' => $item['standar'],
                'order' => $i + 1,
            ]);
        }

        // MONTHLY templates
        $monthlyItems = [
            ['lokasi' => 'PV Module', 'item' => 'Measurement Tegangan DC Cable', 'metode' => 'Action & Monitoring', 'standar' => 'Unbalance < 5%'],
            ['lokasi' => 'PV Module', 'item' => 'Measurement Arus DC Cable', 'metode' => 'Action & Monitoring', 'standar' => 'Unbalance < 5%'],
            ['lokasi' => 'PV Module', 'item' => 'Thermal Monitoring DC Cable', 'metode' => 'Action & Monitoring', 'standar' => 'Max 70°C'],
            ['lokasi' => 'Inverter', 'item' => 'Checking and Measuring Grounding', 'metode' => 'Action & Monitoring', 'standar' => 'Max 5 Ohm'],
            ['lokasi' => 'Inverter', 'item' => 'Thermal Monitoring AC Cable', 'metode' => 'Visual Check & Thermal Test', 'standar' => 'Max 70°C'],
            ['lokasi' => 'Inverter', 'item' => 'Thermal Monitoring DC Cable', 'metode' => 'Visual Check & Thermal Test', 'standar' => 'Max 70°C'],
            ['lokasi' => 'Inverter', 'item' => 'Checking Condition AC Cable', 'metode' => 'Action & Monitoring', 'standar' => 'Tidak Rusak/Terbakar'],
            ['lokasi' => 'Inverter', 'item' => 'Checking Termination AC Cable', 'metode' => 'Action & Monitoring', 'standar' => 'Tidak Bergeser, Tidak Ada Anomali'],
            ['lokasi' => 'Panel LV', 'item' => 'Thermal Monitoring AC Cable at Peak Hour', 'metode' => 'Visual Check & Thermal Test', 'standar' => 'Max 70°C'],
            ['lokasi' => 'Panel LV', 'item' => 'Thermal Monitoring Busbar at Peak Hour', 'metode' => 'Visual Check & Thermal Test', 'standar' => 'Max 70°C'],
            ['lokasi' => 'Panel LV', 'item' => 'Thermal Monitoring Scun Cable at Peak Hour', 'metode' => 'Visual Check & Thermal Test', 'standar' => 'Max 70°C'],
            ['lokasi' => 'Panel LV', 'item' => 'Thermal Monitoring Material Protection', 'metode' => 'Visual Check & Thermal Test', 'standar' => 'Max 70°C'],
        ];

        foreach ($monthlyItems as $i => $item) {
            ChecksheetTemplate::create([
                'checksheet_type_id' => $monthly->id,
                'lokasi_inspeksi' => $item['lokasi'],
                'item_inspeksi' => $item['item'],
                'metode_inspeksi' => $item['metode'],
                'standar_ketentuan' => $item['standar'],
                'order' => $i + 1,
            ]);
        }

        // SEMESTER templates
        $semesterItems = [
            ['lokasi' => 'Inverter', 'item' => 'Checking and Cleaning Fan, Inlet, and Outlet Air of Inverter', 'metode' => 'Action & Monitoring', 'standar' => 'Tidak Kotor'],
            ['lokasi' => 'Panel LV', 'item' => 'Checking and Cleaning Fan, Inlet, and Outlet Fan', 'metode' => 'Action & Monitoring', 'standar' => 'Tidak Kotor'],
            ['lokasi' => 'Panel LV', 'item' => 'Checking Torque All Bolt', 'metode' => 'Action & Monitoring', 'standar' => 'Sesuai Standar Torque'],
        ];

        foreach ($semesterItems as $i => $item) {
            ChecksheetTemplate::create([
                'checksheet_type_id' => $semester->id,
                'lokasi_inspeksi' => $item['lokasi'],
                'item_inspeksi' => $item['item'],
                'metode_inspeksi' => $item['metode'],
                'standar_ketentuan' => $item['standar'],
                'order' => $i + 1,
            ]);
        }

        // ANNUAL templates
        $annualItems = [
            ['lokasi' => 'Transformer', 'item' => 'Purifying Transformator Oil', 'metode' => 'Action & Monitoring', 'standar' => 'Oli Bersih, Tidak Berkurang dari 1150 Liter'],
            ['lokasi' => 'Transformer', 'item' => 'BDV Test', 'metode' => 'Action & Monitoring', 'standar' => '>30 kV/2.5 mm'],
            ['lokasi' => 'Transformer', 'item' => 'DGA Test', 'metode' => 'Action & Monitoring', 'standar' => 'Gas dalam Oli Harus Kondisi Low'],
            ['lokasi' => 'Transformer', 'item' => 'Marking and Tightening Connection Check', 'metode' => 'Action & Monitoring', 'standar' => 'Tidak Bergeser'],
        ];

        foreach ($annualItems as $i => $item) {
            ChecksheetTemplate::create([
                'checksheet_type_id' => $annual->id,
                'lokasi_inspeksi' => $item['lokasi'],
                'item_inspeksi' => $item['item'],
                'metode_inspeksi' => $item['metode'],
                'standar_ketentuan' => $item['standar'],
                'order' => $i + 1,
            ]);
        }

        // Seed sample sessions
        $now = Carbon::now();
        $teknisi = User::where('role', 'technician')->first();
        $spv = User::where('role', 'supervisor')->first();

        $pltsLocations = ['PLTS Pertiwi Lestari', 'PLTS Rengiat', 'PLTS Demo Site'];

        // Submitted weekly session
        $session1 = ChecksheetSession::create([
            'checksheet_type_id' => $weekly->id,
            'plts_location' => $pltsLocations[0],
            'equipment_location' => 'Gedung Utama',
            'period_label' => 'Week 1 - ' . $now->format('M Y'),
            'year' => $now->year,
            'week_number' => 1,
            'month' => $now->month,
            'status' => 'submitted',
            'submitted_at' => $now->subDays(2),
            'submitted_by' => $teknisi?->id,
            'signed_by_teknisi' => $teknisi?->name ?? 'Teknisi 1',
            'signed_date_teknisi' => $now->subDays(2)->toDateString(),
            'signed_by_spv' => $spv?->name ?? 'SPV 1',
            'signed_date_spv' => $now->subDays(1)->toDateString(),
        ]);

        // Add results for session1
        $weeklyTemplates = ChecksheetTemplate::where('checksheet_type_id', $weekly->id)->get();
        foreach ($weeklyTemplates as $idx => $template) {
            ChecksheetResult::create([
                'session_id' => $session1->id,
                'template_id' => $template->id,
                'result' => $idx === 2 ? 'X' : 'P',
                'notes' => $idx === 2 ? 'Ditemukan kabel yang terkelupas, perlu penggantian segera.' : null,
            ]);
        }

        // Add abnormal for session1
        ChecksheetAbnormal::create([
            'session_id' => $session1->id,
            'tanggal' => $now->subDays(2)->toDateString(),
            'abnormal_description' => 'Kabel DC String terkelupas',
            'penanganan' => 'Penggantian kabel dijadwalkan',
            'tgl_selesai' => $now->addDays(5)->toDateString(),
            'pic' => $teknisi?->name ?? 'Teknisi 1',
        ]);

        // Draft weekly session
        ChecksheetSession::create([
            'checksheet_type_id' => $weekly->id,
            'plts_location' => $pltsLocations[1],
            'equipment_location' => 'Area Inverter',
            'period_label' => 'Week ' . $now->weekOfMonth . ' - ' . $now->format('M Y'),
            'year' => $now->year,
            'week_number' => $now->weekOfMonth,
            'month' => $now->month,
            'status' => 'draft',
        ]);

        // Monthly session
        $session3 = ChecksheetSession::create([
            'checksheet_type_id' => $monthly->id,
            'plts_location' => $pltsLocations[0],
            'equipment_location' => 'Panel LV Room',
            'period_label' => $now->format('F Y'),
            'year' => $now->year,
            'month' => $now->month,
            'status' => 'submitted',
            'submitted_at' => $now->subDays(5),
            'submitted_by' => $teknisi?->id,
            'signed_by_teknisi' => $teknisi?->name ?? 'Teknisi 1',
            'signed_date_teknisi' => $now->subDays(5)->toDateString(),
        ]);

        $monthlyTemplates = ChecksheetTemplate::where('checksheet_type_id', $monthly->id)->get();
        foreach ($monthlyTemplates as $template) {
            ChecksheetResult::create([
                'session_id' => $session3->id,
                'template_id' => $template->id,
                'result' => 'P',
            ]);
        }

        // Semester draft
        ChecksheetSession::create([
            'checksheet_type_id' => $semester->id,
            'plts_location' => $pltsLocations[2],
            'equipment_location' => 'Control Room',
            'period_label' => 'Semester 1 ' . $now->year,
            'year' => $now->year,
            'semester' => 1,
            'status' => 'draft',
        ]);

        // Annual draft
        ChecksheetSession::create([
            'checksheet_type_id' => $annual->id,
            'plts_location' => $pltsLocations[0],
            'equipment_location' => 'Transformer Area',
            'period_label' => (string)$now->year,
            'year' => $now->year,
            'status' => 'draft',
        ]);
    }
}
