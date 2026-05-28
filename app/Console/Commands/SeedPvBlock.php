<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Location;
use Illuminate\Console\Command;

class SeedPvBlock extends Command
{
    protected $signature = 'cmms:seed-pv-block
                            {file : Path to CSV (transformer_block,string_number,module_slot,visual_row,visual_col)}
                            {location_id : ID of the PLTS Location}
                            {--brand=Jinko Solar : Panel brand}
                            {--model=JKM550M-72HL4-V : Panel model}
                            {--price=4200000 : Purchase price per unit (IDR)}';

    protected $description = 'Create PV Module assets from a CSV layout file for a given PLTS location';

    public function handle(): int
    {
        $path       = $this->argument('file');
        $locationId = (int) $this->argument('location_id');

        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return 1;
        }

        $location = Location::find($locationId);
        if (!$location) {
            $this->error("Location ID {$locationId} not found.");
            return 1;
        }

        $handle = fopen($path, 'r');
        fgetcsv($handle); // skip header

        $created = 0;
        $updated = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) continue;

            [$block, $string, $slot, $visualRow, $visualCol] = $row;
            $block  = trim($block);
            $string = (int) $string;
            $slot   = (int) $slot;

            $strPad  = str_pad($string, 2, '0', STR_PAD_LEFT);
            $slotPad = str_pad($slot,   2, '0', STR_PAD_LEFT);
            $code    = "{$block}-INV{$strPad}-S{$slotPad}";

            $existing = Asset::where('transformer_block', $block)
                ->where('string_number', $string)
                ->where('module_slot', $slot)
                ->first();

            if ($existing) {
                $existing->update([
                    'visual_row' => (int) $visualRow,
                    'visual_col' => (int) $visualCol,
                ]);
                $updated++;
            } else {
                Asset::create([
                    'asset_code'        => $code,
                    'location_id'       => $locationId,
                    'name'              => "PV Module {$code}",
                    'category'          => 'PV Module',
                    'location'          => "Ground Array {$block}",
                    'status'            => 'active',
                    'brand'             => $this->option('brand'),
                    'model'             => $this->option('model'),
                    'serial_number'     => "SN-{$code}",
                    'purchase_date'     => '2024-03-01',
                    'purchase_price'    => (int) $this->option('price'),
                    'warranty_expiry'   => '2049-03-01',
                    'description'       => "PV module INV{$strPad}-S{$slotPad}, blok {$block}",
                    'transformer_block' => $block,
                    'string_number'     => $string,
                    'module_slot'       => $slot,
                    'visual_row'        => (int) $visualRow,
                    'visual_col'        => (int) $visualCol,
                ]);
                $created++;
            }
        }

        fclose($handle);

        $this->info("PLTS   : {$location->name} (ID: {$locationId})");
        $this->info("Created: {$created}");
        $this->info("Updated: {$updated}");

        return 0;
    }
}
