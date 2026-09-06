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

            $partCode = 'PART-' . str_pad($no, 4, '0', STR_PAD_LEFT);
            $part = SparePart::where('part_code', $partCode)->first();
            $oldQty = $part ? (int) $part->qty_actual : 0;
            $newQty = (int)($row['qty'] ?? 0);

            $part = SparePart::updateOrCreate(
                ['part_code' => $partCode],
                [
                    'name' => $row['nama_barang'] ?? 'Unnamed Part',
                    'qty_actual' => $newQty,
                    'unit' => $row['satuan'] ?? 'Pcs',
                    'location' => $row['letak_barang'] ?? '',
                    'supplier' => $row['pengadaan'] ?? '',
                    'description' => "Brand: " . ($row['merekbrand'] ?? '') . ". " . ($row['keterangan'] ?? ''),
                ]
            );

            if ($newQty !== $oldQty) {
                $diff = abs($newQty - $oldQty);
                \App\Models\StockMovement::create([
                    'spare_part_id' => $part->id,
                    'mutation_type' => $newQty > $oldQty ? 'add' : 'deduct',
                    'qty' => $diff,
                    'reason' => 'import_csv',
                    'created_by_user_id' => auth()->id() ?? 1,
                ]);
            }
        }
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';'
        ];
    }
}
