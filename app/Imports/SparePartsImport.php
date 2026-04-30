<?php

namespace App\Imports;

use App\Models\SparePart;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class SparePartsImport implements ToCollection, WithHeadingRow, WithCustomCsvSettings
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $no = $row['no'] ?? null;
            if (!$no) continue;

            SparePart::updateOrCreate(
                ['part_code' => 'PART-' . str_pad($no, 4, '0', STR_PAD_LEFT)],
                [
                    'name' => $row['nama_barang'] ?? 'Unnamed Part',
                    'qty_actual' => (int)($row['qty'] ?? 0),
                    'unit' => $row['satuan'] ?? 'Pcs',
                    'location' => $row['letak_barang'] ?? '',
                    'supplier' => $row['pengadaan'] ?? '',
                    'description' => "Brand: " . ($row['merekbrand'] ?? '') . ". " . ($row['keterangan'] ?? ''),
                ]
            );
        }
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';'
        ];
    }
}
