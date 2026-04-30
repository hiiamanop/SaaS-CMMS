<?php

namespace App\Imports;

use App\Models\Tool;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class ToolsImport implements ToCollection, WithHeadingRow, WithCustomCsvSettings
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $no = $row['no'] ?? null;
            if (!$no) continue;

            $conditionRaw = strtolower($row['kondisi_barang'] ?? 'bagus');
            $condition = 'good';
            if (str_contains($conditionRaw, 'rusak')) $condition = 'damaged';

            Tool::updateOrCreate(
                ['tool_code' => 'TOOL-' . str_pad($no, 4, '0', STR_PAD_LEFT)],
                [
                    'name' => $row['nama_barang'] ?? 'Unnamed Tool',
                    'brand' => $row['merekbrand'] ?? '',
                    'qty_total' => (int)($row['qty'] ?? 1),
                    'qty_available' => (int)($row['qty'] ?? 1),
                    'location' => $row['letak_barang'] ?? '',
                    'condition' => $condition,
                    'description' => "Unit: " . ($row['satuan'] ?? 'Pcs') . ". " . ($row['keterangan'] ?? ''),
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
