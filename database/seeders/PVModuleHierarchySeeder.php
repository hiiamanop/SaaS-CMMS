<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Location;
use Illuminate\Database\Seeder;

class PVModuleHierarchySeeder extends Seeder
{
    public function run(): void
    {
        // Get or find a location for PLTS
        $location = Location::where('name', 'PLTS')->first();

        if (!$location) {
            $location = Location::create([
                'name' => 'PLTS',
                'address' => 'Solar Power Plant',
                'is_active' => true,
            ]);
        }

        // Create PV modules with hierarchy structure
        // Based on diagram: T01-249 block with:
        // - 12 strings (N01-N12)
        // - ~20-21 modules per string (S01-S21)

        $transformerBlocks = ['T01', 'T02']; // Can be extended
        $stringsPerBlock = 12;
        $modulesPerString = 21;

        foreach ($transformerBlocks as $block) {
            for ($stringNum = 1; $stringNum <= $stringsPerBlock; $stringNum++) {
                for ($moduleNum = 1; $moduleNum <= $modulesPerString; $moduleNum++) {
                    $stringPadded = str_pad($stringNum, 2, '0', STR_PAD_LEFT);
                    $modulePadded = str_pad($moduleNum, 2, '0', STR_PAD_LEFT);
                    $assetCode = "{$block}-N{$stringPadded}-S{$modulePadded}";

                    // Check if asset already exists
                    if (Asset::where('asset_code', $assetCode)->exists()) {
                        continue;
                    }

                    Asset::create([
                        'asset_code'       => $assetCode,
                        'location_id'      => $location->id,
                        'name'             => "PV Module {$assetCode}",
                        'category'         => 'PV Module',
                        'location'         => "Rooftop {$block}",
                        'status'           => 'active',
                        'brand'            => 'Jinko Solar',
                        'model'            => 'JKM550M-72HL4-V',
                        'serial_number'    => "PV-{$assetCode}",
                        'purchase_date'    => '2024-03-01',
                        'purchase_price'   => 4200000,
                        'warranty_expiry'  => '2049-03-01',
                        'description'      => "Individual PV module in string {$stringNum}, block {$block}",
                        'transformer_block' => $block,
                        'string_number'    => $stringNum,
                        'module_slot'      => $moduleNum,
                    ]);
                }
            }
        }
    }
}
