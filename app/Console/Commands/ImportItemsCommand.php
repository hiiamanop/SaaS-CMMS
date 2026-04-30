<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tool;
use App\Models\SparePart;
use App\Models\Consumable;

class ImportItemsCommand extends Command
{
    protected $signature = 'app:import-items';
    protected $description = 'Import tools, spare parts, and consumables from CSV files in docs/ directory';

    public function handle()
    {
        $this->info('Starting import...');

        $this->importTools();
        $this->importSpareParts();
        $this->importConsumables();

        $this->info('Import completed successfully.');
    }

    private function importTools()
    {
        $path = base_path('docs/Stock Opname-Tools.csv');
        if (!file_exists($path)) {
            $this->error("File not found: $path");
            return;
        }

        $handle = fopen($path, 'r');
        fgetcsv($handle, 0, ';'); // Skip header

        $count = 0;
        while (($data = fgetcsv($handle, 0, ';')) !== FALSE) {
            if (count($data) < 4) continue;

            $no = trim($data[0]);
            $name = trim($data[1]);
            $brand = trim($data[2]);
            $qty = (int) trim($data[3]);
            $unit = trim($data[4] ?? 'Pcs');
            $location = trim($data[6] ?? '');
            $conditionRaw = strtolower(trim($data[8] ?? 'bagus'));
            $condition = 'good';
            if (str_contains($conditionRaw, 'rusak')) $condition = 'damaged';
            
            $description = trim($data[9] ?? '');

            Tool::updateOrCreate(
                ['tool_code' => 'TOOL-' . str_pad($no, 4, '0', STR_PAD_LEFT)],
                [
                    'name' => $name,
                    'brand' => $brand,
                    'qty_total' => $qty,
                    'qty_available' => $qty,
                    'location' => $location,
                    'condition' => $condition,
                    'description' => "Unit: $unit. $description",
                ]
            );
            $count++;
        }
        fclose($handle);
        $this->line("Imported $count Tools.");
    }

    private function importSpareParts()
    {
        $path = base_path('docs/Stock Opname-SparePart.csv');
        if (!file_exists($path)) {
            $this->error("File not found: $path");
            return;
        }

        $handle = fopen($path, 'r');
        fgetcsv($handle, 0, ';'); // Skip header

        $count = 0;
        while (($data = fgetcsv($handle, 0, ';')) !== FALSE) {
            if (count($data) < 4) continue;

            $no = trim($data[0]);
            $name = trim($data[1]);
            $brand = trim($data[2]);
            $qty = (int) trim($data[3]);
            $unit = trim($data[4] ?? 'Pcs');
            $location = trim($data[6] ?? '');
            $supplier = trim($data[7] ?? '');
            $description = trim($data[9] ?? '');

            SparePart::updateOrCreate(
                ['part_code' => 'PART-' . str_pad($no, 4, '0', STR_PAD_LEFT)],
                [
                    'name' => $name,
                    'qty_actual' => $qty,
                    'unit' => $unit,
                    'location' => $location,
                    'supplier' => $supplier,
                    'description' => "Brand: $brand. $description",
                ]
            );
            $count++;
        }
        fclose($handle);
        $this->line("Imported $count Spare Parts.");
    }

    private function importConsumables()
    {
        $path = base_path('docs/Stock Opname-Consumable.csv');
        if (!file_exists($path)) {
            $this->error("File not found: $path");
            return;
        }

        $handle = fopen($path, 'r');
        fgetcsv($handle, 0, ';'); // Skip header

        $count = 0;
        while (($data = fgetcsv($handle, 0, ';')) !== FALSE) {
            if (count($data) < 4) continue;

            $no = trim($data[0]);
            $name = trim($data[1]);
            $brand = trim($data[2]);
            $qty = (int) trim($data[3]);
            $unit = trim($data[4] ?? 'Pcs');
            $location = trim($data[6] ?? '');
            $supplier = trim($data[7] ?? '');
            $description = trim($data[9] ?? '');

            Consumable::updateOrCreate(
                ['item_code' => 'CONS-' . str_pad($no, 4, '0', STR_PAD_LEFT)],
                [
                    'name' => $name,
                    'qty_actual' => $qty,
                    'unit' => $unit,
                    'location' => $location,
                    'supplier' => $supplier,
                    'description' => "Brand: $brand. $description",
                ]
            );
            $count++;
        }
        fclose($handle);
        $this->line("Imported $count Consumables.");
    }
}
