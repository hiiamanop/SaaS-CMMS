<?php

namespace Database\Seeders;

use App\Models\ChecksheetTemplate;
use Illuminate\Database\Seeder;

class ChecksheetTemplateSeeder extends Seeder
{
    /**
     * Schedule IDs berdasarkan urutan insert di MaintenanceScheduleSeeder:
     *  1 = PV Module 01 Mingguan
     *  2 = PV Module 01 Bulanan
     *  3 = PV Module 01 Semesteran
     *  4 = PV Module 02 Mingguan
     *  5 = Inverter 01 Mingguan
     *  6 = Inverter 01 Bulanan
     *  7 = Inverter 01 Semesteran
     *  8 = Inverter 02 Mingguan
     *  9 = Inverter 02 Bulanan
     * 10 = Panel LV 01 Mingguan
     * 11 = Panel LV 01 Bulanan
     * 12 = Panel LV 01 Semesteran
     * 13 = Transformer 01 Bulanan
     * 14 = Transformer 01 Tahunan
     * 15 = Transformer 02 Bulanan
     * 16 = Transformer 02 Tahunan
     */
    public function run(): void
    {
        $items = [

            // ─────────────────────────────────────────────────────
            // SCHEDULE 1 — PV Module Area 01 Mingguan
            // ─────────────────────────────────────────────────────
            [1, 'PV Module Area 01', 'Kondisi fisik panel (retak, hotspot, jamur)',          'Visual',        'Tidak ada kerusakan/bercak', 1],
            [1, 'PV Module Area 01', 'Kebersihan permukaan panel dari debu & kotoran',       'Visual',        'Bersih, tidak ada lapisan debu tebal', 2],
            [1, 'PV Module Area 01', 'Kondisi frame & mounting bracket',                     'Visual',        'Tidak ada karat, baut tidak kendur', 3],
            [1, 'PV Module Area 01', 'Kondisi kabel DC & MC4 connector',                     'Visual',        'Tidak ada isolasi retak, konektor terkunci', 4],
            [1, 'String Combiner',   'Kondisi pintu & seal combiner box',                    'Visual',        'Tidak ada kerusakan, seal rapat', 1],
            [1, 'String Combiner',   'Indikator LED status pada combiner box',               'Visual',        'Semua indikator normal (hijau)', 2],

            // ─────────────────────────────────────────────────────
            // SCHEDULE 2 — PV Module Area 01 Bulanan
            // ─────────────────────────────────────────────────────
            [2, 'PV Module Area 01', 'Tegangan DC open-circuit per string (Voc)',            'Pengukuran',    'Voc ±5% dari nilai nominal', 1],
            [2, 'PV Module Area 01', 'Arus DC short-circuit per string (Isc)',               'Pengukuran',    'Isc ±5% antar string seragam', 2],
            [2, 'PV Module Area 01', 'Pemeriksaan semua MC4 connector (kekencangan)',        'Pengencangan',  'Tidak ada connector longgar, terkunci sempurna', 3],
            [2, 'PV Module Area 01', 'Pembersihan permukaan panel dengan air bersih',        'Pembersihan',   'Selesai, debu terangkat', 4],
            [2, 'String Combiner',   'Pengukuran tegangan string pada combiner',             'Pengukuran',    'Selisih antar string < 5%', 1],
            [2, 'String Combiner',   'Kondisi fuse per string (visual & uji kontinuitas)',   'Visual/Uji',    'Semua fuse utuh dan kontinuitas baik', 2],
            [2, 'String Combiner',   'Kondisi SPD DC (indikator)',                           'Visual',        'Indikator hijau, tidak merah', 3],
            [2, 'Grounding',         'Pengukuran resistansi grounding sistem',               'Pengukuran',    'Resistansi grounding < 5 ohm', 1],

            // ─────────────────────────────────────────────────────
            // SCHEDULE 4 — PV Module Area 02 Mingguan
            // ─────────────────────────────────────────────────────
            [4, 'PV Module Area 02', 'Kondisi fisik panel (retak, hotspot, jamur)',          'Visual',        'Tidak ada kerusakan/bercak', 1],
            [4, 'PV Module Area 02', 'Kebersihan permukaan panel dari debu & kotoran',       'Visual',        'Bersih, tidak ada lapisan debu tebal', 2],
            [4, 'PV Module Area 02', 'Kondisi frame & mounting bracket',                     'Visual',        'Tidak ada karat, baut tidak kendur', 3],
            [4, 'PV Module Area 02', 'Kondisi kabel DC & MC4 connector',                     'Visual',        'Tidak ada isolasi retak, konektor terkunci', 4],
            [4, 'String Combiner',   'Kondisi pintu & seal combiner box',                    'Visual',        'Tidak ada kerusakan, seal rapat', 1],
            [4, 'String Combiner',   'Indikator LED status pada combiner box',               'Visual',        'Semua indikator normal (hijau)', 2],

            // ─────────────────────────────────────────────────────
            // SCHEDULE 5 — Inverter 01 Mingguan
            // ─────────────────────────────────────────────────────
            [5, 'Inverter 01',       'Display inverter (status operasi, alarm aktif)',        'Visual',        'Status normal, tidak ada alarm aktif', 1],
            [5, 'Inverter 01',       'Suhu operasi inverter (via display)',                  'Visual',        'Suhu < 65°C', 2],
            [5, 'Inverter 01',       'Kondisi fisik body inverter',                          'Visual',        'Tidak ada kerusakan, ventilasi tidak terhalang', 3],
            [5, 'Inverter 01',       'Bunyi abnormal dari inverter (dengung, klik)',          'Pendengaran',   'Tidak ada bunyi abnormal', 4],
            [5, 'Kabel AC/DC',       'Kondisi kabel AC output (isolasi, konektor)',          'Visual',        'Isolasi utuh, tidak ada tanda panas', 1],
            [5, 'Kabel AC/DC',       'Kondisi kabel DC input (isolasi, MC4)',               'Visual',        'Isolasi utuh, MC4 terkunci', 2],

            // ─────────────────────────────────────────────────────
            // SCHEDULE 6 — Inverter 01 Bulanan
            // ─────────────────────────────────────────────────────
            [6, 'Inverter 01',       'Kondisi fan pendingin (kebersihan, putaran)',           'Visual/Uji',    'Fan berputar normal, tidak tersumbat debu', 1],
            [6, 'Inverter 01',       'Filter udara inlet & outlet (kebersihan)',              'Visual',        'Filter bersih, tidak tersumbat', 2],
            [6, 'Inverter 01',       'Pengencangan torque terminal DC & AC',                 'Torque wrench', 'Sesuai spec torque pabrikan', 3],
            [6, 'Inverter 01',       'Pembacaan error log / event log',                      'Software',      'Tidak ada error kritis dalam 30 hari terakhir', 4],
            [6, 'Grounding',         'Pengukuran resistansi grounding inverter',              'Pengukuran',    'Resistansi < 1 ohm', 1],
            [6, 'Grounding',         'Kondisi fisik kabel grounding & klem',                 'Visual',        'Kabel utuh, klem kencang tidak berkarat', 2],

            // ─────────────────────────────────────────────────────
            // SCHEDULE 8 — Inverter 02 Mingguan
            // ─────────────────────────────────────────────────────
            [8, 'Inverter 02',       'Display inverter (status operasi, alarm aktif)',        'Visual',        'Status normal, tidak ada alarm aktif', 1],
            [8, 'Inverter 02',       'Suhu operasi inverter (via display)',                  'Visual',        'Suhu < 65°C', 2],
            [8, 'Inverter 02',       'Kondisi fisik body inverter',                          'Visual',        'Tidak ada kerusakan, ventilasi tidak terhalang', 3],
            [8, 'Inverter 02',       'Bunyi abnormal dari inverter',                         'Pendengaran',   'Tidak ada bunyi abnormal', 4],
            [8, 'Kabel AC/DC',       'Kondisi kabel AC output',                              'Visual',        'Isolasi utuh, tidak ada tanda panas', 1],
            [8, 'Kabel AC/DC',       'Kondisi kabel DC input',                               'Visual',        'Isolasi utuh, MC4 terkunci', 2],

            // ─────────────────────────────────────────────────────
            // SCHEDULE 9 — Inverter 02 Bulanan
            // ─────────────────────────────────────────────────────
            [9, 'Inverter 02',       'Kondisi fan pendingin (kebersihan, putaran)',           'Visual/Uji',    'Fan berputar normal, tidak tersumbat debu', 1],
            [9, 'Inverter 02',       'Filter udara inlet & outlet (kebersihan)',              'Visual',        'Filter bersih, tidak tersumbat', 2],
            [9, 'Inverter 02',       'Pengencangan torque terminal DC & AC',                 'Torque wrench', 'Sesuai spec torque pabrikan', 3],
            [9, 'Grounding',         'Pengukuran resistansi grounding inverter',              'Pengukuran',    'Resistansi < 1 ohm', 1],
            [9, 'Grounding',         'Kondisi kabel grounding & klem',                       'Visual',        'Kabel utuh, klem kencang', 2],

            // ─────────────────────────────────────────────────────
            // SCHEDULE 10 — Panel LV 01 Mingguan
            // ─────────────────────────────────────────────────────
            [10, 'Panel LV 01',      'Kondisi SPD AC (indikator warna)',                     'Visual',        'Indikator hijau, tidak merah', 1],
            [10, 'Panel LV 01',      'Status semua MCCB & MCB (posisi on/off/trip)',          'Visual',        'Semua sesuai posisi normal', 2],
            [10, 'Panel LV 01',      'Lampu indikator panel (R, S, T)',                      'Visual',        'Semua menyala normal', 3],
            [10, 'Panel LV 01',      'Kondisi UPS panel (charging status)',                  'Visual',        'Status charging normal', 4],
            [10, 'Panel LV 01',      'Kondisi fisik panel (pintu, kunci, seal kabel)',        'Visual',        'Tidak ada kerusakan, pintu terkunci', 5],
            [10, 'Panel LV 01',      'Bunyi abnormal dari panel (busbar, kontaktor)',         'Pendengaran',   'Tidak ada bunyi abnormal', 6],

            // ─────────────────────────────────────────────────────
            // SCHEDULE 11 — Panel LV 01 Bulanan
            // ─────────────────────────────────────────────────────
            [11, 'Panel LV 01',      'Thermal monitoring busbar fasa R, S, T',               'Thermal camera','Suhu busbar < 60°C, delta < 10°C antar fasa', 1],
            [11, 'Panel LV 01',      'Thermal monitoring kabel AC main',                     'Thermal camera','Tidak ada titik panas (hotspot)', 2],
            [11, 'Panel LV 01',      'Pengencangan baut busbar & terminal',                  'Torque wrench', 'Sesuai torque spec, tidak kendur', 3],
            [11, 'Panel LV 01',      'Pengecekan kondisi fuse & fuse holder',                'Visual',        'Fuse utuh, holder tidak hangus', 4],
            [11, 'Proteksi',         'Pengujian trip MCCB utama (manual test)',               'Uji fungsi',    'Trip berfungsi normal, reset ok', 1],
            [11, 'Proteksi',         'Pengecekan kondisi SPD AC (fisik & indikator)',         'Visual',        'Tidak hangus, indikator hijau', 2],

            // ─────────────────────────────────────────────────────
            // SCHEDULE 13 — Transformer 01 Bulanan
            // ─────────────────────────────────────────────────────
            [13, 'Transformer 01',   'Level minyak trafo (sight glass)',                     'Visual',        'Level minyak dalam batas normal (tanda MIN-MAX)', 1],
            [13, 'Transformer 01',   'Suhu winding & minyak (thermometer)',                  'Visual',        'Suhu winding < 65°C, minyak < 55°C', 2],
            [13, 'Transformer 01',   'Kondisi konservator (level minyak ekspansi)',           'Visual',        'Level minyak di atas tanda MIN', 3],
            [13, 'Transformer 01',   'Kebocoran minyak (gasket, valve, sambungan)',           'Visual',        'Tidak ada rembesan/tetesan minyak', 4],
            [13, 'Transformer 01',   'Kondisi bushing HV & LV (retak, kotor)',               'Visual',        'Tidak ada retak, permukaan bersih', 5],
            [13, 'Transformer 01',   'Kondisi breather silica gel (warna)',                  'Visual',        'Warna biru/merah muda (aktif), bukan putih', 6],
            [13, 'Grounding',        'Kondisi kabel grounding trafo & klem',                 'Visual',        'Kabel utuh, klem kencang tidak berkarat', 1],

            // ─────────────────────────────────────────────────────
            // SCHEDULE 14 — Transformer 01 Tahunan
            // ─────────────────────────────────────────────────────
            [14, 'Minyak Trafo',     'BDV test minyak trafo (breakdown voltage)',            'Uji laboratorium','BDV > 30 kV (IEC 60156)', 1],
            [14, 'Minyak Trafo',     'DGA test (dissolved gas analysis)',                    'Uji laboratorium','Tidak ada gas berbahaya (H2, CO2) melebihi batas', 2],
            [14, 'Minyak Trafo',     'Pengisian/penggantian minyak jika diperlukan',         'Prosedur',      'Level minyak kembali normal pasca purifikasi', 3],
            [14, 'Transformer 01',   'Pengencangan semua baut & konektor (tightening check)','Torque wrench', 'Sesuai spec torque pabrikan', 1],
            [14, 'Transformer 01',   'Uji rasio belitan (TTR test)',                         'TTR meter',     'Rasio sesuai nameplate ±0.5%', 2],
            [14, 'Transformer 01',   'Uji resistansi insulasi winding',                      'Megger',        'IR > 1000 MΩ (PI > 1.5)', 3],
            [14, 'Breaker/Proteksi', 'Uji fungsi protection relay & setting',                'Uji fungsi',    'Relay bekerja sesuai setting koordinasi', 1],

            // ─────────────────────────────────────────────────────
            // SCHEDULE 15 — Transformer 02 Bulanan
            // ─────────────────────────────────────────────────────
            [15, 'Transformer 02',   'Level minyak trafo (sight glass)',                     'Visual',        'Level minyak dalam batas normal', 1],
            [15, 'Transformer 02',   'Suhu winding & minyak (thermometer)',                  'Visual',        'Suhu winding < 65°C, minyak < 55°C', 2],
            [15, 'Transformer 02',   'Kondisi konservator',                                  'Visual',        'Level minyak di atas tanda MIN', 3],
            [15, 'Transformer 02',   'Kebocoran minyak (gasket, valve)',                     'Visual',        'Tidak ada rembesan', 4],
            [15, 'Transformer 02',   'Kondisi bushing HV & LV',                              'Visual',        'Tidak ada retak, bersih', 5],
            [15, 'Transformer 02',   'Kondisi breather silica gel',                          'Visual',        'Warna aktif (biru/merah muda)', 6],
        ];

        foreach ($items as [$schedId, $lokasi, $item, $metode, $standar, $order]) {
            ChecksheetTemplate::create([
                'maintenance_schedule_id' => $schedId,
                'lokasi_inspeksi'         => $lokasi,
                'item_inspeksi'           => $item,
                'metode_inspeksi'         => $metode,
                'standar_ketentuan'       => $standar,
                'order'                   => $order,
            ]);
        }
    }
}
