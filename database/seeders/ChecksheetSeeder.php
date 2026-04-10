<?php

namespace Database\Seeders;

use App\Models\ChecksheetAbnormal;
use App\Models\ChecksheetResult;
use App\Models\ChecksheetSession;
use App\Models\ChecksheetTemplate;
use App\Models\ChecksheetType;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChecksheetSeeder extends Seeder
{
    public function run(): void
    {
        $teknisi1 = User::where('email', 'teknisi1@cmms.com')->first();
        $teknisi2 = User::where('email', 'teknisi2@cmms.com')->first();
        $spv      = User::where('email', 'spv@cmms.com')->first();
        $pm       = User::where('email', 'pm@cmms.com')->first();

        // ═══════════════════════════════════════════════════════════
        //  1. BUAT TIPE CHECKSHEET
        // ═══════════════════════════════════════════════════════════
        $weekly   = ChecksheetType::create(['name' => 'Mingguan',   'frequency' => 'weekly']);
        $monthly  = ChecksheetType::create(['name' => 'Bulanan',    'frequency' => 'monthly']);
        $semester = ChecksheetType::create(['name' => 'Semesteran', 'frequency' => 'semester']);
        $annual   = ChecksheetType::create(['name' => 'Tahunan',    'frequency' => 'yearly']);

        // ═══════════════════════════════════════════════════════════
        //  2. BUAT TEMPLATE (FORM ITEMS) PER TIPE
        //     Seperti membuat soal di Google Form
        // ═══════════════════════════════════════════════════════════

        // ── MINGGUAN ─────────────────────────────────────────────
        $weeklyItems = [
            // PV Module
            ['lokasi' => 'PV Module',  'item' => 'Kondisi kebersihan permukaan PV Module',             'metode' => 'Visual Check',                   'standar' => 'Bersih, tidak ada debu/kotoran menumpuk'],
            ['lokasi' => 'PV Module',  'item' => 'Kondisi fisik & mounting PV Module',                 'metode' => 'Visual Check',                   'standar' => 'Tidak ada modul retak, mounting tidak bergeser'],
            ['lokasi' => 'PV Module',  'item' => 'Kondisi kabel DC String',                            'metode' => 'Visual Check',                   'standar' => 'Tidak terkelupas/robek, rapih & terikat'],
            ['lokasi' => 'PV Module',  'item' => 'Kondisi MC4 Connector antar string',                 'metode' => 'Visual Check',                   'standar' => 'Tidak longgar, tidak ada tanda terbakar'],
            ['lokasi' => 'PV Module',  'item' => 'Kondisi grounding PV Module',                       'metode' => 'Visual Check',                   'standar' => 'Kabel grounding terhubung, tidak terlepas'],
            ['lokasi' => 'PV Module',  'item' => 'Kondisi bracing, end clamp & mid clamp',            'metode' => 'Visual Check',                   'standar' => 'Tidak terlepas, tidak berkarat parah'],
            // Inverter
            ['lokasi' => 'Inverter',   'item' => 'Kebersihan bodi & ruang inverter',                  'metode' => 'Visual Check & Pembersihan',      'standar' => 'Bersih, tidak ada debu tebal di ventilasi'],
            ['lokasi' => 'Inverter',   'item' => 'Kondisi MC4 Connector di sisi inverter',            'metode' => 'Visual Check & Thermal Test',     'standar' => 'Tidak rusak/terbakar, tidak kendur'],
            ['lokasi' => 'Inverter',   'item' => 'Thermal check MC4 Connector',                       'metode' => 'Thermal Imaging',                 'standar' => '≤60°C Normal | 60–70°C Warning | >70°C Critical'],
            ['lokasi' => 'Inverter',   'item' => 'Torque kabel AC sisi LV inverter',                  'metode' => 'Visual Check',                   'standar' => 'Tidak kendur, sesuai standar torque'],
            ['lokasi' => 'Inverter',   'item' => 'Cek historical alarm & fault code inverter',        'metode' => 'Pengecekan Software/Display',     'standar' => 'Tidak ada alarm aktif yang belum ditangani'],
            ['lokasi' => 'Inverter',   'item' => 'Indikator status & display inverter',               'metode' => 'Visual Check',                   'standar' => 'Running normal, display terbaca jelas'],
            // Panel LV
            ['lokasi' => 'Panel LV',   'item' => 'Kondisi SPD (Surge Protective Device)',             'metode' => 'Visual Check',                   'standar' => 'Indikator hijau, koneksi grounding baik'],
            ['lokasi' => 'Panel LV',   'item' => 'Kondisi visual MCCB & posisi handle',               'metode' => 'Visual Check',                   'standar' => 'Handle ON, tidak ada indikator trip'],
            ['lokasi' => 'Panel LV',   'item' => 'Kondisi CB control & wiring panel',                 'metode' => 'Visual Check',                   'standar' => 'Tidak ada kabel terlepas/terbakar'],
            ['lokasi' => 'Panel LV',   'item' => 'Status UPS & kondisi baterai UPS',                  'metode' => 'Visual Check',                   'standar' => 'Tidak ada alarm UPS, baterai terhubung'],
        ];

        foreach ($weeklyItems as $i => $item) {
            ChecksheetTemplate::create([
                'checksheet_type_id' => $weekly->id,
                'lokasi_inspeksi'    => $item['lokasi'],
                'item_inspeksi'      => $item['item'],
                'metode_inspeksi'    => $item['metode'],
                'standar_ketentuan'  => $item['standar'],
                'order'              => $i + 1,
            ]);
        }

        // ── BULANAN ──────────────────────────────────────────────
        $monthlyItems = [
            // PV Module
            ['lokasi' => 'PV Module',  'item' => 'Measurement tegangan DC per string',                'metode' => 'Pengukuran Multimeter/Clamp Meter','standar' => 'Unbalance antar string < 5%'],
            ['lokasi' => 'PV Module',  'item' => 'Measurement arus DC per string',                   'metode' => 'Pengukuran Clamp Meter',          'standar' => 'Unbalance antar string < 5%'],
            ['lokasi' => 'PV Module',  'item' => 'Thermal monitoring kabel DC string',               'metode' => 'Thermal Imaging',                 'standar' => 'Maksimal 70°C'],
            ['lokasi' => 'PV Module',  'item' => 'Kondisi isolasi kabel & joint connector',          'metode' => 'Visual Check',                   'standar' => 'Tidak ada retak/sobek pada isolasi'],
            // Inverter
            ['lokasi' => 'Inverter',   'item' => 'Checking & measuring resistansi grounding inverter','metode' => 'Pengukuran Earth Tester',         'standar' => 'Resistansi grounding ≤ 1 Ohm'],
            ['lokasi' => 'Inverter',   'item' => 'Thermal monitoring kabel AC sisi LV',              'metode' => 'Thermal Imaging',                 'standar' => 'Maksimal 70°C'],
            ['lokasi' => 'Inverter',   'item' => 'Thermal monitoring kabel DC input inverter',       'metode' => 'Thermal Imaging',                 'standar' => 'Maksimal 70°C'],
            ['lokasi' => 'Inverter',   'item' => 'Kondisi terminasi kabel AC (torque & visual)',      'metode' => 'Visual Check & Torque Check',     'standar' => 'Tidak kendur, tidak ada anomali'],
            ['lokasi' => 'Inverter',   'item' => 'Performa output inverter (efisiensi)',              'metode' => 'Monitoring Data SCADA',           'standar' => 'Efisiensi ≥ 97%'],
            // Panel LV
            ['lokasi' => 'Panel LV',   'item' => 'Thermal monitoring kabel AC di peak hour',         'metode' => 'Thermal Imaging',                 'standar' => 'Maksimal 70°C'],
            ['lokasi' => 'Panel LV',   'item' => 'Thermal monitoring busbar panel di peak hour',     'metode' => 'Thermal Imaging',                 'standar' => 'Maksimal 70°C'],
            ['lokasi' => 'Panel LV',   'item' => 'Thermal monitoring scun/klem kabel',               'metode' => 'Thermal Imaging',                 'standar' => 'Maksimal 70°C'],
            ['lokasi' => 'Panel LV',   'item' => 'Thermal monitoring material proteksi (MCCB, CB)',  'metode' => 'Thermal Imaging',                 'standar' => 'Maksimal 70°C'],
            // Transformer
            ['lokasi' => 'Transformer','item' => 'Level minyak transformator',                       'metode' => 'Visual Check pada sight glass',   'standar' => 'Level antara MIN–MAX pada sight glass'],
            ['lokasi' => 'Transformer','item' => 'Kondisi fisik & kebocoran minyak trafo',           'metode' => 'Visual Check',                   'standar' => 'Tidak ada rembesan/kebocoran'],
            ['lokasi' => 'Transformer','item' => 'Suhu operasi transformator',                       'metode' => 'Monitoring thermometer/SCADA',    'standar' => 'Maksimal 75°C (winding temperature)'],
        ];

        foreach ($monthlyItems as $i => $item) {
            ChecksheetTemplate::create([
                'checksheet_type_id' => $monthly->id,
                'lokasi_inspeksi'    => $item['lokasi'],
                'item_inspeksi'      => $item['item'],
                'metode_inspeksi'    => $item['metode'],
                'standar_ketentuan'  => $item['standar'],
                'order'              => $i + 1,
            ]);
        }

        // ── SEMESTERAN ────────────────────────────────────────────
        $semesterItems = [
            // PV Module
            ['lokasi' => 'PV Module',  'item' => 'Pembersihan menyeluruh permukaan PV Module',       'metode' => 'Pembersihan dengan air & sabun',  'standar' => 'Bersih menyeluruh, tidak ada noda membandel'],
            ['lokasi' => 'PV Module',  'item' => 'Pengecekan & pengetatan mounting structure',       'metode' => 'Torque Check & Visual',           'standar' => 'Semua baut dikencangkan sesuai spesifikasi'],
            ['lokasi' => 'PV Module',  'item' => 'Insulation resistance test kabel DC string',      'metode' => 'Insulation Resistance Tester',    'standar' => '> 1 MΩ pada 500V DC'],
            // Inverter
            ['lokasi' => 'Inverter',   'item' => 'Cleaning fan, inlet & outlet air inverter',       'metode' => 'Pembersihan dengan compressed air','standar' => 'Bersih, tidak ada debu menyumbat'],
            ['lokasi' => 'Inverter',   'item' => 'Pengecekan kondisi kapasitor DC Link',             'metode' => 'Visual Check & Pengukuran',       'standar' => 'Tidak ada fisik kembung/bocor, nilai kapasitansi normal'],
            ['lokasi' => 'Inverter',   'item' => 'Update firmware inverter (jika tersedia)',         'metode' => 'Software Update via Laptop',      'standar' => 'Firmware versi terbaru terinstal'],
            ['lokasi' => 'Inverter',   'item' => 'Uji insulation resistance terminal DC & AC',      'metode' => 'Insulation Resistance Tester',    'standar' => '> 1 MΩ pada 500V DC'],
            // Panel LV
            ['lokasi' => 'Panel LV',   'item' => 'Cleaning fan & ventilasi panel LV',               'metode' => 'Pembersihan dengan compressed air','standar' => 'Bersih, tidak ada debu'],
            ['lokasi' => 'Panel LV',   'item' => 'Torque all bolt & klem busbar panel LV',          'metode' => 'Torque Wrench sesuai spesifikasi', 'standar' => 'Semua baut sesuai nilai torque standar'],
            ['lokasi' => 'Panel LV',   'item' => 'Uji fungsi trip MCCB (manual & remote)',          'metode' => 'Function Test',                  'standar' => 'Trip & reset berhasil pada semua MCCB'],
            ['lokasi' => 'Panel LV',   'item' => 'Penggantian baterai UPS (jika kapasitas < 80%)',  'metode' => 'Battery Capacity Test',           'standar' => 'Kapasitas baterai UPS ≥ 80%'],
        ];

        foreach ($semesterItems as $i => $item) {
            ChecksheetTemplate::create([
                'checksheet_type_id' => $semester->id,
                'lokasi_inspeksi'    => $item['lokasi'],
                'item_inspeksi'      => $item['item'],
                'metode_inspeksi'    => $item['metode'],
                'standar_ketentuan'  => $item['standar'],
                'order'              => $i + 1,
            ]);
        }

        // ── TAHUNAN ──────────────────────────────────────────────
        $annualItems = [
            // Transformer
            ['lokasi' => 'Transformer','item' => 'Purifying minyak transformator',                   'metode' => 'Oil Purification Machine',        'standar' => 'Oli bersih, level normal (min. 1150 liter)'],
            ['lokasi' => 'Transformer','item' => 'BDV Test (Breakdown Voltage Test)',                 'metode' => 'BDV Tester sesuai IEC 60156',     'standar' => '> 30 kV/2.5 mm'],
            ['lokasi' => 'Transformer','item' => 'DGA Test (Dissolved Gas Analysis)',                 'metode' => 'Sample minyak ke lab & analisis', 'standar' => 'Kandungan gas terlarut kondisi Low/Normal'],
            ['lokasi' => 'Transformer','item' => 'Marking & tightening connection check',            'metode' => 'Visual Check & Torque Check',     'standar' => 'Tidak bergeser, semua baut kencang'],
            ['lokasi' => 'Transformer','item' => 'Uji rasio belitan (Turns Ratio Test)',             'metode' => 'TTR Meter',                       'standar' => 'Ratio sesuai nameplate ± 0.5%'],
            ['lokasi' => 'Transformer','item' => 'Pengecekan kondisi bushing tegangan tinggi',       'metode' => 'Visual Check & Insulation Test',  'standar' => 'Tidak retak, resistansi isolasi > 100 MΩ'],
            // Panel LV
            ['lokasi' => 'Panel LV',   'item' => 'Uji koordinasi proteksi (MCCB & relay)',           'metode' => 'Injection Test',                 'standar' => 'Waktu trip sesuai setting koordinasi'],
            ['lokasi' => 'Panel LV',   'item' => 'Kalibrasi energy meter & power quality analyzer',  'metode' => 'Kalibrasi dengan reference meter','standar' => 'Error < 0.5% dari reference'],
            // Inverter
            ['lokasi' => 'Inverter',   'item' => 'Overhaul lengkap inverter (cleaning board & komponen)','metode' => 'Pembersihan & pengecekan menyeluruh','standar' => 'Semua komponen bersih, tidak ada anomali'],
            ['lokasi' => 'Inverter',   'item' => 'Uji anti-islanding protection',                   'metode' => 'Function Test',                  'standar' => 'Inverter trip dalam < 2 detik saat grid hilang'],
        ];

        foreach ($annualItems as $i => $item) {
            ChecksheetTemplate::create([
                'checksheet_type_id' => $annual->id,
                'lokasi_inspeksi'    => $item['lokasi'],
                'item_inspeksi'      => $item['item'],
                'metode_inspeksi'    => $item['metode'],
                'standar_ketentuan'  => $item['standar'],
                'order'              => $i + 1,
            ]);
        }

        // ═══════════════════════════════════════════════════════════
        //  3. BUAT SESI PENGISIAN (Histori Jan–Apr 2026)
        //     Simulasi teknisi sudah mengisi form secara berkala
        // ═══════════════════════════════════════════════════════════

        $weeklyTemplates   = ChecksheetTemplate::where('checksheet_type_id', $weekly->id)->get();
        $monthlyTemplates  = ChecksheetTemplate::where('checksheet_type_id', $monthly->id)->get();
        $semesterTemplates = ChecksheetTemplate::where('checksheet_type_id', $semester->id)->get();
        $annualTemplates   = ChecksheetTemplate::where('checksheet_type_id', $annual->id)->get();

        // Helper: isi semua item dengan P, kecuali index yg ditentukan = X + catatan
        $fillSession = function (ChecksheetSession $session, $templates, array $abnormalIdx = []) {
            foreach ($templates as $idx => $tpl) {
                $isAbnormal = in_array($idx, array_keys($abnormalIdx));
                ChecksheetResult::create([
                    'session_id'  => $session->id,
                    'template_id' => $tpl->id,
                    'result'      => $isAbnormal ? 'X' : 'P',
                    'notes'       => $isAbnormal ? $abnormalIdx[$idx] : null,
                ]);
            }
        };

        // ─── MINGGUAN ────────────────────────────────────────────
        // Januari 2026
        $this->weeklySession($weekly->id, 'PLTS Gedung A', 2026, 1, 1, 'W1 Januari 2026', $teknisi1, $spv, $pm,
            '2026-01-07', '2026-01-07', '2026-01-08', '2026-01-09', $fillSession, $weeklyTemplates, []);

        $this->weeklySession($weekly->id, 'PLTS Gedung A', 2026, 1, 2, 'W2 Januari 2026', $teknisi1, $spv, $pm,
            '2026-01-14', '2026-01-14', '2026-01-15', '2026-01-15', $fillSession, $weeklyTemplates,
            [2 => 'Kabel DC string 03 ditemukan terkelupas pada beberapa titik, perlu penggantian.']);

        $this->weeklySession($weekly->id, 'PLTS Gedung A', 2026, 1, 3, 'W3 Januari 2026', $teknisi1, $spv, $pm,
            '2026-01-21', '2026-01-21', '2026-01-22', '2026-01-22', $fillSession, $weeklyTemplates, []);

        $this->weeklySession($weekly->id, 'PLTS Gedung A', 2026, 1, 4, 'W4 Januari 2026', $teknisi1, $spv, $pm,
            '2026-01-28', '2026-01-28', '2026-01-29', '2026-01-29', $fillSession, $weeklyTemplates, []);

        // Februari 2026
        $this->weeklySession($weekly->id, 'PLTS Gedung A', 2026, 2, 1, 'W1 Februari 2026', $teknisi2, $spv, $pm,
            '2026-02-04', '2026-02-04', '2026-02-05', '2026-02-05', $fillSession, $weeklyTemplates, []);

        $this->weeklySession($weekly->id, 'PLTS Gedung A', 2026, 2, 2, 'W2 Februari 2026', $teknisi2, $spv, $pm,
            '2026-02-11', '2026-02-11', '2026-02-12', '2026-02-12', $fillSession, $weeklyTemplates,
            [8 => 'Thermal MC4 inverter 02 menunjukkan 65°C (Warning). Dilaporkan ke SPV.']);

        $this->weeklySession($weekly->id, 'PLTS Gedung A', 2026, 2, 3, 'W3 Februari 2026', $teknisi2, $spv, $pm,
            '2026-02-18', '2026-02-18', '2026-02-19', '2026-02-19', $fillSession, $weeklyTemplates, []);

        $this->weeklySession($weekly->id, 'PLTS Gedung A', 2026, 2, 4, 'W4 Februari 2026', $teknisi2, $spv, $pm,
            '2026-02-25', '2026-02-25', '2026-02-26', '2026-02-26', $fillSession, $weeklyTemplates, []);

        // Maret 2026
        $this->weeklySession($weekly->id, 'PLTS Gedung A', 2026, 3, 1, 'W1 Maret 2026', $teknisi1, $spv, $pm,
            '2026-03-04', '2026-03-04', '2026-03-05', '2026-03-05', $fillSession, $weeklyTemplates, []);

        $this->weeklySession($weekly->id, 'PLTS Gedung A', 2026, 3, 2, 'W2 Maret 2026', $teknisi1, $spv, $pm,
            '2026-03-11', '2026-03-11', '2026-03-12', '2026-03-12', $fillSession, $weeklyTemplates,
            [12 => 'Indikator MCCB Panel LV 01 menunjukkan tanda trip. WO corrective dibuat.']);

        $this->weeklySession($weekly->id, 'PLTS Gedung A', 2026, 3, 3, 'W3 Maret 2026', $teknisi1, $spv, $pm,
            '2026-03-18', '2026-03-18', '2026-03-19', '2026-03-19', $fillSession, $weeklyTemplates, []);

        $this->weeklySession($weekly->id, 'PLTS Gedung A', 2026, 3, 4, 'W4 Maret 2026', $teknisi1, $spv, $pm,
            '2026-03-25', '2026-03-25', '2026-03-26', '2026-03-26', $fillSession, $weeklyTemplates, []);

        // April 2026 W1 — draft (belum di-submit)
        ChecksheetSession::create([
            'checksheet_type_id' => $weekly->id,
            'plts_location'      => 'PLTS Gedung A',
            'period_label'       => 'W1 April 2026',
            'year'               => 2026,
            'week_number'        => 1,
            'month'              => 4,
            'status'             => 'draft',
        ]);

        // ─── BULANAN ─────────────────────────────────────────────
        $this->monthlySession($monthly->id, 'PLTS Gedung A', 2026, 1, 'Januari 2026', $teknisi1, $spv, $pm,
            '2026-01-20', '2026-01-20', '2026-01-21', '2026-01-22', $fillSession, $monthlyTemplates, []);

        $this->monthlySession($monthly->id, 'PLTS Gedung A', 2026, 2, 'Februari 2026', $teknisi2, $spv, $pm,
            '2026-02-17', '2026-02-17', '2026-02-18', '2026-02-19', $fillSession, $monthlyTemplates,
            [4 => 'Resistansi grounding inverter 02 menunjukkan 1.8 Ohm (di atas batas 1 Ohm). Dijadwalkan perbaikan.']);

        $this->monthlySession($monthly->id, 'PLTS Gedung A', 2026, 3, 'Maret 2026', $teknisi1, $spv, $pm,
            '2026-03-17', '2026-03-17', '2026-03-18', '2026-03-19', $fillSession, $monthlyTemplates,
            [13 => 'Level minyak trafo 01 berada di bawah batas MIN. Ditemukan rembesan pada gasket.']);

        // April — draft
        ChecksheetSession::create([
            'checksheet_type_id' => $monthly->id,
            'plts_location'      => 'PLTS Gedung A',
            'period_label'       => 'April 2026',
            'year'               => 2026,
            'month'              => 4,
            'status'             => 'draft',
        ]);

        // ─── SEMESTERAN ──────────────────────────────────────────
        // Draft untuk Semester 1 2026 (belum saatnya)
        ChecksheetSession::create([
            'checksheet_type_id' => $semester->id,
            'plts_location'      => 'PLTS Gedung A',
            'period_label'       => 'Semester 1 — 2026',
            'year'               => 2026,
            'semester'           => 1,
            'status'             => 'draft',
        ]);

        // ─── TAHUNAN ─────────────────────────────────────────────
        // Draft untuk 2026
        ChecksheetSession::create([
            'checksheet_type_id' => $annual->id,
            'plts_location'      => 'PLTS Gedung A',
            'period_label'       => '2026',
            'year'               => 2026,
            'status'             => 'draft',
        ]);

        // ═══════════════════════════════════════════════════════════
        //  4. CATATAN ABNORMAL
        // ═══════════════════════════════════════════════════════════
        // Ambil session W2 Jan (id urutan ke-2), W2 Feb, W2 Mar, Bulanan Feb
        $sessions = ChecksheetSession::where('status', 'submitted')->orderBy('id')->get();

        // W2 Januari — kabel DC terkelupas
        ChecksheetAbnormal::create([
            'session_id'          => $sessions->get(1)->id,
            'tanggal'             => '2026-01-14',
            'abnormal_description'=> 'Kabel DC string 03 terkelupas pada ±30cm dekat combiner box',
            'penanganan'          => 'Dibuat WO corrective WO-202601-0001, kabel diganti tanggal 2026-01-10',
            'tgl_selesai'         => '2026-01-10',
            'pic'                 => $teknisi1?->name ?? 'Andi Teknisi',
        ]);

        // W2 Februari — thermal MC4 warning
        ChecksheetAbnormal::create([
            'session_id'          => $sessions->get(5)->id,
            'tanggal'             => '2026-02-11',
            'abnormal_description'=> 'Suhu MC4 connector inverter 02 menunjukkan 65°C (Warning zone)',
            'penanganan'          => 'Connector dikencangkan dan dibersihkan, suhu turun ke 52°C pada pemeriksaan berikutnya',
            'tgl_selesai'         => '2026-02-18',
            'pic'                 => $teknisi2?->name ?? 'Rudi Teknisi',
        ]);

        // W2 Maret — MCCB indikator trip
        ChecksheetAbnormal::create([
            'session_id'          => $sessions->get(9)->id,
            'tanggal'             => '2026-03-11',
            'abnormal_description'=> 'MCCB 250A Panel LV 01 menunjukkan indikator trip, tidak dapat di-reset',
            'penanganan'          => 'Dibuat WO corrective WO-202602-0003. MCCB diganti tanggal 2026-02-19',
            'tgl_selesai'         => '2026-02-19',
            'pic'                 => $teknisi1?->name ?? 'Andi Teknisi',
        ]);
    }

    // Helper: buat sesi mingguan submitted lengkap
    private function weeklySession(
        int $typeId, string $location, int $year, int $month, int $week, string $label,
        $teknisi, $spv, $pm,
        string $submitDate, string $ttdTeknisi, string $ttdSpv, string $ttdPm,
        callable $fill, $templates, array $abnormalIdx
    ): ChecksheetSession {
        $session = ChecksheetSession::create([
            'checksheet_type_id' => $typeId,
            'plts_location'      => $location,
            'period_label'       => $label,
            'year'               => $year,
            'month'              => $month,
            'week_number'        => $week,
            'status'             => 'submitted',
            'submitted_at'       => $submitDate . ' 16:00:00',
            'submitted_by'       => $teknisi?->id,
            'signed_by_teknisi'  => $teknisi?->name,
            'signed_date_teknisi'=> $ttdTeknisi,
            'signed_by_spv'      => $spv?->name,
            'signed_date_spv'    => $ttdSpv,
            'signed_by_pm'       => $pm?->name,
            'signed_date_pm'     => $ttdPm,
        ]);
        $fill($session, $templates, $abnormalIdx);
        return $session;
    }

    // Helper: buat sesi bulanan submitted lengkap
    private function monthlySession(
        int $typeId, string $location, int $year, int $month, string $label,
        $teknisi, $spv, $pm,
        string $submitDate, string $ttdTeknisi, string $ttdSpv, string $ttdPm,
        callable $fill, $templates, array $abnormalIdx
    ): ChecksheetSession {
        $session = ChecksheetSession::create([
            'checksheet_type_id' => $typeId,
            'plts_location'      => $location,
            'period_label'       => $label,
            'year'               => $year,
            'month'              => $month,
            'status'             => 'submitted',
            'submitted_at'       => $submitDate . ' 17:00:00',
            'submitted_by'       => $teknisi?->id,
            'signed_by_teknisi'  => $teknisi?->name,
            'signed_date_teknisi'=> $ttdTeknisi,
            'signed_by_spv'      => $spv?->name,
            'signed_date_spv'    => $ttdSpv,
            'signed_by_pm'       => $pm?->name,
            'signed_date_pm'     => $ttdPm,
        ]);
        $fill($session, $templates, $abnormalIdx);
        return $session;
    }
}
