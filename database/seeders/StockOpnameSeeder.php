<?php

namespace Database\Seeders;

use App\Models\Consumable;
use App\Models\SparePart;
use App\Models\StockMovement;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockOpnameSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('email', 'wakwaw@gmail.com')->value('id')
            ?? User::first()?->id
            ?? 1;

        $this->seedConsumables();
        $this->seedSpareParts($adminId);
        $this->seedTools();
    }

    private function seedConsumables(): void
    {
        $path = base_path('docs/Stock Opname-Consumable.csv');
        if (!file_exists($path)) {
            $this->command->warn("Consumables CSV not found at: {$path}");
            return;
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 0, ';', '"', '\\');
        $count = 0;

        while (($row = fgetcsv($handle, 0, ';', '"', '\\')) !== false) {
            $no = trim($row[0] ?? '');
            if (!$no || !is_numeric($no)) {
                continue;
            }

            $name        = trim($row[1] ?? 'Unnamed Consumable');
            $brand       = trim($row[2] ?? '');
            $qty         = (int) trim($row[3] ?? 0);
            $unit        = trim($row[4] ?? 'pcs');
            $dateSo      = trim($row[5] ?? '');
            $location    = trim($row[6] ?? '');
            $supplier    = trim($row[7] ?? '');
            $condition   = trim($row[8] ?? '');
            $notes       = trim($row[9] ?? '');

            $category = $this->categorizeConsumable($name);
            $code = 'CONS-' . str_pad($no, 4, '0', STR_PAD_LEFT);

            $descParts = array_filter([
                $brand ? "Brand: {$brand}" : null,
                $condition ? "Kondisi: {$condition}" : null,
                $dateSo ? "Tanggal SO: {$dateSo}" : null,
                $notes ? "Keterangan: {$notes}" : null,
            ]);

            Consumable::updateOrCreate(
                ['item_code' => $code],
                [
                    'name'        => $name,
                    'category'    => $category,
                    'unit'        => $unit ?: 'pcs',
                    'qty_actual'  => $qty,
                    'qty_minimum' => max(1, (int) round($qty * 0.2)),
                    'supplier'    => $supplier ?: 'AHP',
                    'location'    => $location ?: 'Gudang',
                    'description' => implode(' | ', $descParts),
                ]
            );
            $count++;
        }
        fclose($handle);
        $this->command->info("✓ Seeded {$count} Consumables from Stock Opname CSV.");
    }

    private function seedSpareParts(int $adminId): void
    {
        $path = base_path('docs/Stock Opname-SparePart.csv');
        if (!file_exists($path)) {
            $this->command->warn("Spare Parts CSV not found at: {$path}");
            return;
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 0, ';', '"', '\\');
        $count = 0;

        while (($row = fgetcsv($handle, 0, ';', '"', '\\')) !== false) {
            $no = trim($row[0] ?? '');
            if (!$no || !is_numeric($no)) {
                continue;
            }

            $name        = trim($row[1] ?? 'Unnamed Spare Part');
            $brand       = trim($row[2] ?? '');
            $rawQty      = trim($row[3] ?? '');
            $qty         = $rawQty !== '' ? (int) $rawQty : 0;
            $unit        = trim($row[4] ?? 'pcs');
            $dateSo      = trim($row[5] ?? '');
            $location    = trim($row[6] ?? '');
            $supplier    = trim($row[7] ?? '');
            $condition   = trim($row[8] ?? '');
            $notes       = trim($row[9] ?? '');

            $category = $this->categorizeSparePart($name);
            $code = 'PART-' . str_pad($no, 4, '0', STR_PAD_LEFT);

            $descParts = array_filter([
                $brand ? "Brand: {$brand}" : null,
                $condition ? "Kondisi: {$condition}" : null,
                $dateSo ? "Tanggal SO: {$dateSo}" : null,
                $notes ? "Keterangan: {$notes}" : null,
            ]);

            $part = SparePart::updateOrCreate(
                ['part_code' => $code],
                [
                    'name'        => $name,
                    'category'    => $category,
                    'unit'        => $unit ?: 'pcs',
                    'qty_actual'  => $qty,
                    'qty_minimum' => max(1, (int) round($qty * 0.2)),
                    'supplier'    => $supplier ?: 'Hypec / AHP',
                    'location'    => $location ?: 'Gudang',
                    'description' => implode(' | ', $descParts),
                ]
            );

            // Record initial stock movement if not present
            StockMovement::firstOrCreate(
                [
                    'spare_part_id' => $part->id,
                    'reason'        => 'initial_stock_opname',
                ],
                [
                    'mutation_type'      => 'add',
                    'qty'                => $qty,
                    'created_by_user_id' => $adminId,
                ]
            );

            $count++;
        }
        fclose($handle);
        $this->command->info("✓ Seeded {$count} Spare Parts from Stock Opname CSV.");
    }

    private function seedTools(): void
    {
        $path = base_path('docs/Stock Opname-Tools.csv');
        if (!file_exists($path)) {
            $this->command->warn("Tools CSV not found at: {$path}");
            return;
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, 0, ';', '"', '\\');
        $count = 0;

        while (($row = fgetcsv($handle, 0, ';', '"', '\\')) !== false) {
            $no = trim($row[0] ?? '');
            if (!$no || !is_numeric($no)) {
                continue;
            }

            $name        = trim($row[1] ?? 'Unnamed Tool');
            $brand       = trim($row[2] ?? '');
            $qty         = (int) trim($row[3] ?? 1);
            $unit        = trim($row[4] ?? 'pcs');
            $dateSo      = trim($row[5] ?? '');
            $location    = trim($row[6] ?? '');
            $supplier    = trim($row[7] ?? '');
            $rawCond     = strtolower(trim($row[8] ?? 'bagus'));
            $notes       = trim($row[9] ?? '');

            $condition = 'good';
            if (str_contains($rawCond, 'rusak')) {
                $condition = 'damaged';
            } elseif (str_contains($rawCond, 'hilang')) {
                $condition = 'lost';
            }

            $qtyAvailable = $qty;
            if (preg_match('/(\d+)\s*dipinjam/i', $notes, $m)) {
                $qtyAvailable = max(0, $qty - (int) $m[1]);
            } elseif ($condition === 'damaged' || $condition === 'lost') {
                $qtyAvailable = 0;
            }

            $category = $this->categorizeTool($name);
            $code = 'TOOL-' . str_pad($no, 4, '0', STR_PAD_LEFT);

            $descParts = array_filter([
                $supplier ? "Pengadaan: {$supplier}" : null,
                $unit ? "Satuan: {$unit}" : null,
                $dateSo ? "Tanggal SO: {$dateSo}" : null,
                $notes ? "Keterangan: {$notes}" : null,
            ]);

            Tool::updateOrCreate(
                ['tool_code' => $code],
                [
                    'name'          => $name,
                    'category'      => $category,
                    'brand'         => $brand ?: 'No Brand',
                    'condition'     => $condition,
                    'qty_total'     => $qty,
                    'qty_available' => $qtyAvailable,
                    'location'      => $location ?: 'Rak Tools',
                    'description'   => implode(' | ', $descParts),
                ]
            );
            $count++;
        }
        fclose($handle);
        $this->command->info("✓ Seeded {$count} Tools from Stock Opname CSV.");
    }

    private function categorizeConsumable(string $name): string
    {
        $n = strtolower($name);
        if (preg_match('/pel|sapu|sikat|wiper|plastik|kantong|kanebo|keset|wipol|sunlight|majun|refil/i', $n)) {
            return 'Cleaning & Sanitation';
        }
        if (preg_match('/sepatu|helm|kacamata|sarung tangan|rompi|boots/i', $n)) {
            return 'Safety & APD';
        }
        if (preg_match('/cat|amplas|kuas|elastex|zincromate|nippe|aerosol/i', $n)) {
            return 'Painting & Coating';
        }
        if (preg_match('/grease|oli|pelumas/i', $n)) {
            return 'Lubrication';
        }
        if (preg_match('/isolasi|tape|kabel ties|skun|vinyl|elektroda|kawat las/i', $n)) {
            return 'Electrical Consumables';
        }
        return 'General Consumables';
    }

    private function categorizeSparePart(string $name): string
    {
        $n = strtolower($name);
        if (preg_match('/mccb|rcbo|mcb|acb|fuse|switchgear|breaker|shunt|closing|selenoid/i', $n)) {
            return 'Proteksi & Switchgear';
        }
        if (preg_match('/kabel|wire|lan|fo|nym/i', $n)) {
            return 'Kabel & Aksesoris';
        }
        if (preg_match('/mc4|konektor/i', $n)) {
            return 'Konektor PV';
        }
        if (preg_match('/bolt|nut|baut|drat|skun|busbar|terminal block|endplate|gembok/i', $n)) {
            return 'Mechanical & Hardware';
        }
        if (preg_match('/meter|sensor|pilot lamp|temperature|pressure|volt|ampere/i', $n)) {
            return 'Instrumentation & Metering';
        }
        if (preg_match('/gateway|hub|extender|switch|omada|htb/i', $n)) {
            return 'Network & Monitoring';
        }
        return 'General Spare Parts';
    }

    private function categorizeTool(string $name): string
    {
        $n = strtolower($name);
        if (preg_match('/tester|multimeter|clamp|thermal|thermometer|ols|opm|meteran|penggaris|jangka sorong|seaward|califer/i', $n)) {
            return 'Measuring & Testing';
        }
        if (preg_match('/impact|bor|driil|gerinda|mesin las|blower|cut off|kompresor|rumput/i', $n)) {
            return 'Power Tools';
        }
        if (preg_match('/kunci|tang|obeng|palu|gergaji/i', $n)) {
            return 'Hand Tools';
        }
        return 'General Tools';
    }
}
