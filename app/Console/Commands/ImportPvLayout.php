<?php

namespace App\Console\Commands;

use App\Models\Asset;
use Illuminate\Console\Command;

class ImportPvLayout extends Command
{
    protected $signature = 'cmms:import-pv-layout {file : Path to CSV file (transformer_block,string_number,module_slot,visual_row,visual_col)}';
    protected $description = 'Import visual positions for PV modules from a CSV file';

    public function handle(): int
    {
        $path = $this->argument('file');

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return 1;
        }

        $handle = fopen($path, 'r');
        fgetcsv($handle); // skip header row

        $updated = 0;
        $notFound = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) continue;
            [$block, $string, $slot, $visualRow, $visualCol] = $row;

            $count = Asset::where('transformer_block', trim($block))
                ->where('string_number', (int) $string)
                ->where('module_slot', (int) $slot)
                ->update([
                    'visual_row' => (int) $visualRow,
                    'visual_col' => (int) $visualCol,
                ]);

            $count ? $updated++ : $notFound++;
        }

        fclose($handle);

        $this->info("Done. Updated: {$updated} | Asset not found: {$notFound}");
        return 0;
    }
}
