<?php

namespace Database\Seeders;

use App\Models\MaintenanceRecord;
use App\Models\MaintenanceRecordPart;
use App\Models\SparePart;
use Illuminate\Database\Seeder;

class MaintenanceRecordSeeder extends Seeder
{
    public function run(): void
    {
        // Records hanya dari WO yang sudah closed (WO ID 1–9)
        // Type selalu 'corrective' karena semua WO adalah corrective
        $records = [
            [
                'record_number'    => 'MR-202601-0001',
                'work_order_id'    => 1,
                'asset_id'         => 1,   // PV Module Area 01
                'technician_id'    => 3,   // Andi
                'type'             => 'corrective',
                'maintenance_date' => '2026-01-10',
                'findings'         => 'Kabel DC string 03 putus pada joint ke combiner box. Isolasi kabel retak akibat paparan UV & panas berlebih.',
                'actions_taken'    => 'Kabel DC 4mm² sepanjang 12 meter diganti. Semua joint di-crimp ulang dan dibungkus isolasi UV resistant. Output string kembali normal 5.2kW.',
                'duration_minutes' => 300,
                'downtime_minutes' => 300,
                'notes'            => 'Rekomendasikan pemeriksaan isolasi semua kabel DC Area 01 pada PM bulanan berikutnya.',
            ],
            [
                'record_number'    => 'MR-202601-0002',
                'work_order_id'    => 2,
                'asset_id'         => 2,   // PV Module Area 02
                'technician_id'    => 4,   // Rudi
                'type'             => 'corrective',
                'maintenance_date' => '2026-01-16',
                'findings'         => 'MC4 connector string 07 (4 pasang) terbakar akibat kontak longgar dan overheating. Kerusakan juga merambat ke kabel ±30cm.',
                'actions_taken'    => 'Dipotong bagian kabel yang rusak. 8 pasang MC4 connector baru dipasang. Dilakukan uji tegangan dan arus. Output string 07 normal kembali.',
                'duration_minutes' => 330,
                'downtime_minutes' => 330,
                'notes'            => 'Seluruh MC4 connector string lain diperiksa, tidak ada yang longgar.',
            ],
            [
                'record_number'    => 'MR-202601-0003',
                'work_order_id'    => 3,
                'asset_id'         => 4,   // Inverter 01
                'technician_id'    => 3,
                'type'             => 'corrective',
                'maintenance_date' => '2026-01-21',
                'findings'         => 'Fan pendingin utama Inverter 01 mati total. Suhu operasi inverter mencapai 72°C (batas 65°C). Bearing fan sudah aus.',
                'actions_taken'    => 'Fan inverter SMA 100kW diganti unit baru. Setelah penggantian suhu turun ke 42°C dalam 30 menit. Inverter beroperasi normal.',
                'duration_minutes' => 180,
                'downtime_minutes' => 180,
                'notes'            => 'Fan lama disimpan sebagai spare darurat (masih bisa berputar tapi berisik).',
            ],
            [
                'record_number'    => 'MR-202602-0001',
                'work_order_id'    => 4,
                'asset_id'         => 1,   // PV Module Area 01
                'technician_id'    => 4,
                'type'             => 'corrective',
                'maintenance_date' => '2026-02-06',
                'findings'         => 'SPD DC Type II pada string combiner box Area 01 menunjukkan indikator merah. Modul SPD sudah tidak berfungsi.',
                'actions_taken'    => 'SPD DC lama dilepas dan diganti unit baru SPD DC Type II 1000V. Indikator hijau. Sistem proteksi kembali aktif.',
                'duration_minutes' => 180,
                'downtime_minutes' => 0,
                'notes'            => 'Kemungkinan SPD bekerja saat ada lonjakan tegangan. Periksa log SCADA.',
            ],
            [
                'record_number'    => 'MR-202602-0002',
                'work_order_id'    => 5,
                'asset_id'         => 5,   // Inverter 02
                'technician_id'    => 3,
                'type'             => 'corrective',
                'maintenance_date' => '2026-02-13',
                'findings'         => 'Kabel grounding Inverter 02 terlepas dari terminal klem akibat baut kendur. Nilai resistansi grounding 5.2 ohm (batas < 1 ohm).',
                'actions_taken'    => 'Terminal klem dibersihkan, kabel grounding dipasang kembali dan dikencangkan. Ditambah kabel grounding tambahan 16mm². Nilai resistansi turun ke 0.3 ohm.',
                'duration_minutes' => 210,
                'downtime_minutes' => 90,
                'notes'            => 'Semua baut terminal grounding inverter lain diperiksa dan dikencangkan.',
            ],
            [
                'record_number'    => 'MR-202602-0003',
                'work_order_id'    => 6,
                'asset_id'         => 7,   // Panel LV 01
                'technician_id'    => 4,
                'type'             => 'corrective',
                'maintenance_date' => '2026-02-19',
                'findings'         => 'MCCB 250A Panel LV 01 mengalami internal fault, bimetal strip rusak. Tidak dapat di-reset manual maupun remote.',
                'actions_taken'    => 'MCCB 250A lama dilepas dan diganti unit baru Schneider 250A. Uji trip pada 125%, 150%, dan 200% nominal berhasil. Sistem kembali normal.',
                'duration_minutes' => 420,
                'downtime_minutes' => 420,
                'notes'            => 'MCCB lama disimpan untuk analisis lebih lanjut. Periksa penyebab overload.',
            ],
            [
                'record_number'    => 'MR-202603-0001',
                'work_order_id'    => 7,
                'asset_id'         => 9,   // Transformer 01
                'technician_id'    => 3,
                'type'             => 'corrective',
                'maintenance_date' => '2026-03-09',
                'findings'         => 'Gasket conservator Transformer 01 retak akibat penuaan material. Level minyak turun 8% dari nominal. Ditemukan rembesan minyak di bagian bawah conservator.',
                'actions_taken'    => 'Gasket karet conservator diganti set baru. Minyak trafo ditambah 15 liter hingga level normal. Dilakukan uji kebocoran 24 jam — tidak ada rembesan.',
                'duration_minutes' => 600,
                'downtime_minutes' => 600,
                'notes'            => 'Jadwalkan BDV test minyak pada PM tahunan berikutnya untuk memastikan kualitas minyak tidak terkontaminasi.',
            ],
            [
                'record_number'    => 'MR-202603-0002',
                'work_order_id'    => 8,
                'asset_id'         => 7,   // Panel LV 01
                'technician_id'    => 4,
                'type'             => 'corrective',
                'maintenance_date' => '2026-03-17',
                'findings'         => 'Thermal imaging mendeteksi titik panas 95°C pada busbar fasa R. Ditemukan loose connection pada 3 baut busbar. Bagian busbar fasa R gosong sepanjang 5cm.',
                'actions_taken'    => 'Semua baut busbar dikencangkan dengan torque wrench sesuai spesifikasi. Bagian busbar yang gosong dipotong dan disambung dengan busbar baru. Uji thermal pasca perbaikan: suhu max 42°C.',
                'duration_minutes' => 480,
                'downtime_minutes' => 480,
                'notes'            => 'Rekomendasikan thermal imaging setiap 3 bulan. Semua baut busbar diperiksa ulang.',
            ],
            [
                'record_number'    => 'MR-202603-0003',
                'work_order_id'    => 9,
                'asset_id'         => 5,   // Inverter 02
                'technician_id'    => 3,
                'type'             => 'corrective',
                'maintenance_date' => '2026-03-24',
                'findings'         => 'Filter udara inlet Inverter 02 tersumbat debu tebal. Airflow berkurang ~60%, menyebabkan overtemperature alarm.',
                'actions_taken'    => 'Filter udara inlet dan outlet diganti baru. Bagian dalam inverter dibersihkan dari debu dengan compressed air. Suhu operasi turun ke 38°C.',
                'duration_minutes' => 120,
                'downtime_minutes' => 60,
                'notes'            => 'Frekuensi penggantian filter diubah dari 3 bulan menjadi 2 bulan mengingat kondisi lingkungan berdebu.',
            ],
        ];

        foreach ($records as $record) {
            MaintenanceRecord::create($record);
        }

        // Spare parts yang digunakan (referensi spare part PLTS)
        $partsUsed = [
            // MR-202601-0001: Kabel DC string putus
            ['maintenance_record_id' => 1, 'spare_part_id' => 3,  'qty_used' => 12, 'unit_price' => 18000],  // Kabel DC 4mm² 12m
            ['maintenance_record_id' => 1, 'spare_part_id' => 22, 'qty_used' => 2,  'unit_price' => 35000],  // Isolasi listrik

            // MR-202601-0002: MC4 connector terbakar
            ['maintenance_record_id' => 2, 'spare_part_id' => 1,  'qty_used' => 8,  'unit_price' => 25000],  // MC4 Male
            ['maintenance_record_id' => 2, 'spare_part_id' => 2,  'qty_used' => 8,  'unit_price' => 25000],  // MC4 Female
            ['maintenance_record_id' => 2, 'spare_part_id' => 4,  'qty_used' => 3,  'unit_price' => 18000],  // Kabel DC 6mm²

            // MR-202601-0003: Fan inverter mati
            ['maintenance_record_id' => 3, 'spare_part_id' => 9,  'qty_used' => 1,  'unit_price' => 650000], // Fan Inverter SMA

            // MR-202602-0001: SPD DC rusak
            ['maintenance_record_id' => 4, 'spare_part_id' => 7,  'qty_used' => 1,  'unit_price' => 850000], // SPD DC Type II

            // MR-202602-0002: Grounding inverter terlepas
            ['maintenance_record_id' => 5, 'spare_part_id' => 25, 'qty_used' => 4,  'unit_price' => 55000],  // Kabel grounding 16mm²
            ['maintenance_record_id' => 5, 'spare_part_id' => 24, 'qty_used' => 1,  'unit_price' => 65000],  // Baut stainless

            // MR-202602-0003: MCCB rusak
            ['maintenance_record_id' => 6, 'spare_part_id' => 13, 'qty_used' => 1,  'unit_price' => 3500000],// MCCB 3P 250A

            // MR-202603-0001: Kebocoran minyak trafo
            ['maintenance_record_id' => 7, 'spare_part_id' => 18, 'qty_used' => 1,  'unit_price' => 1800000],// Minyak trafo 20L
            ['maintenance_record_id' => 7, 'spare_part_id' => 19, 'qty_used' => 1,  'unit_price' => 650000], // Gasket karet trafo

            // MR-202603-0002: Busbar gosong
            ['maintenance_record_id' => 8, 'spare_part_id' => 22, 'qty_used' => 2,  'unit_price' => 35000],  // Isolasi listrik
            ['maintenance_record_id' => 8, 'spare_part_id' => 24, 'qty_used' => 1,  'unit_price' => 65000],  // Baut stainless

            // MR-202603-0003: Filter inverter tersumbat
            ['maintenance_record_id' => 9, 'spare_part_id' => 10, 'qty_used' => 2,  'unit_price' => 120000], // Filter Udara Inverter
        ];

        foreach ($partsUsed as $part) {
            $sparePart = SparePart::find($part['spare_part_id']);
            if ($sparePart) {
                $sparePart->decrement('qty_actual', $part['qty_used']);
            }
            MaintenanceRecordPart::create($part);
        }
    }
}
