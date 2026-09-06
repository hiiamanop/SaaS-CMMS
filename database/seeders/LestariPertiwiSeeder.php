<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Location;
use App\Models\PvMap;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LestariPertiwiSeeder extends Seeder
{
    /** Valid blocks for current layout scope */
    public const VALID_BLOCKS = ['T01', 'T02', 'T03', 'T04', 'T05', 'T06', 'T07'];

    public function run(): void
    {
        // 36,014.02 kWp = ~36.014 MWp across T01 through T07
        $location = Location::updateOrCreate(
            ['name' => 'Lestari Pertiwi'],
            [
                'capacity_mwp' => 36.014,
                'address'      => 'Jl. Lestari Pertiwi No. 1, Area PLTS',
                'is_active'    => true,
            ]
        );

        // Clean up any blocks outside valid scope
        Asset::where('location_id', $location->id)
            ->whereNotIn('transformer_block', self::VALID_BLOCKS)
            ->forceDelete();

        PvMap::where('location_id', $location->id)
            ->whereNotIn('transformer_block', self::VALID_BLOCKS)
            ->delete();

        $this->seedPVAssetsFromMap($location->id);
    }

    private function seedPVAssetsFromMap(int $locationId): void
    {
        $csvMapPath = base_path('docs/PV_String_Map_T01-T07.csv');

        if (!file_exists($csvMapPath)) {
            $this->command->error("CSV file not found: {$csvMapPath}");
            return;
        }

        // 1. Load exact visual grid coordinates for T01 to T07
        $layoutFiles = [
            'T01' => 'docs/T01-249_PV_Layout.csv',
            'T02' => 'docs/T02-249_PV_Layout.csv',
            'T03' => 'docs/T03-245_PV_Layout.csv',
            'T04' => 'docs/T04_PV_Layout.csv',
            'T05' => 'docs/T05_PV_Layout.csv',
            'T06' => 'docs/T06-273_PV_Layout.csv',
            'T07' => 'docs/T07-271_PV_Layout.csv',
        ];

        $visualCoordinates = [];
        foreach ($layoutFiles as $blk => $relPath) {
            $vPath = base_path($relPath);
            if (file_exists($vPath)) {
                $vHandle = fopen($vPath, 'r');
                fgetcsv($vHandle); // Header
                while (($vRow = fgetcsv($vHandle)) !== false) {
                    if (count($vRow) >= 5) {
                        $block   = trim($vRow[0]);
                        $invPad  = str_pad((int)$vRow[1], 2, '0', STR_PAD_LEFT);
                        $slotPad = str_pad((int)$vRow[2], 2, '0', STR_PAD_LEFT);
                        $code    = "{$block}-INV{$invPad}-S{$slotPad}";
                        $visualCoordinates[$code] = [
                            'row' => (int)$vRow[3],
                            'col' => (int)$vRow[4],
                        ];
                    }
                }
                fclose($vHandle);
                $this->command->info("Loaded layout file: {$relPath}");
            } else {
                $this->command->warn("Layout file missing: {$relPath}");
            }
        }

        // 2. Read technical string metadata from docs/PV_String_Map_T01-T07.csv
        $handle = fopen($csvMapPath, 'r');

        // Strip UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xef\xbb\xbf") {
            rewind($handle);
        }

        $header = fgetcsv($handle);
        if (!$header) {
            $this->command->error("CSV file is empty or could not be read.");
            fclose($handle);
            return;
        }

        $header = array_map(fn($h) => trim(strtolower($h)), $header);
        $headerMap = array_flip($header);

        $pvModulesData         = [];
        $invertersData         = [];
        $zoneStats             = [];
        $modulesPerBlockForMap = [];

        $now = now();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < count($header)) continue;

            $zone = trim($row[$headerMap['zone'] ?? 0]);

            // Filter ONLY requested blocks: T01, T02, T04, T05
            if (!in_array($zone, self::VALID_BLOCKS)) {
                continue;
            }

            $invNo       = (int) trim($row[$headerMap['inverter_no'] ?? 1]);
            $invId       = trim($row[$headerMap['inverter_id'] ?? 2]);
            $stringNo    = (int) trim($row[$headerMap['string_no'] ?? 3]);
            $stringId    = trim($row[$headerMap['string_id'] ?? 4]);
            $modsPerStr  = (int) trim($row[$headerMap['modules_per_string'] ?? 5]);
            $invStrCount = (int) trim($row[$headerMap['inverter_string_count'] ?? 6]);
            $invKwp      = (float) trim($row[$headerMap['inverter_capacity_kwp'] ?? 8]);
            $cable       = trim($row[$headerMap['cable_cross_section'] ?? 9]);
            $zoneKwp     = (float) trim($row[$headerMap['zone_total_capacity_kwp'] ?? 12]);

            // Track zone statistics
            if (!isset($zoneStats[$zone])) {
                $zoneStats[$zone] = [
                    'capacity_kwp' => $zoneKwp,
                    'inverters'    => [],
                ];
            }
            $zoneStats[$zone]['inverters'][$invId] = true;

            // Determine visual position from layout CSV
            if (isset($visualCoordinates[$stringId])) {
                $visualRow = $visualCoordinates[$stringId]['row'];
                $visualCol = $visualCoordinates[$stringId]['col'];
            } else {
                $visualRow = $invNo;
                $visualCol = $stringNo;
            }

            // PV Module String Asset
            $pvModulesData[] = [
                'asset_code'        => $stringId,
                'location_id'       => $locationId,
                'name'              => "PV Module {$stringId}",
                'category'          => 'PV Module',
                'location'          => "Ground Array {$zone}",
                'status'            => 'active',
                'brand'             => 'Jinko Solar',
                'model'             => 'JKM550M-72HL4-V',
                'serial_number'     => "SN-{$stringId}",
                'purchase_date'     => '2024-03-01',
                'purchase_price'    => 4200000,
                'warranty_expiry'   => '2049-03-01',
                'description'       => "PV Module {$stringId} ({$modsPerStr} panel seri x 550Wp, cable: {$cable})",
                'transformer_block' => $zone,
                'string_number'     => $invNo,
                'module_slot'       => $stringNo,
                'visual_row'        => $visualRow,
                'visual_col'        => $visualCol,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];

            $modulesPerBlockForMap[$zone][] = [
                'asset_code'        => $stringId,
                'transformer_block' => $zone,
                'string_number'     => $invNo,
                'module_slot'       => $stringNo,
                'visual_row'        => $visualRow,
                'visual_col'        => $visualCol,
                'status'            => 'active',
            ];

            // Inverter Asset (deduplicated)
            if (!isset($invertersData[$invId])) {
                $invertersData[$invId] = [
                    'asset_code'        => $invId,
                    'location_id'       => $locationId,
                    'name'              => "Inverter {$invId}",
                    'category'          => 'Inverter',
                    'location'          => "Area PLTS {$zone}",
                    'status'            => 'active',
                    'brand'             => 'Huawei',
                    'model'             => 'SUN2000-330KTL-H1',
                    'serial_number'     => "SN-{$invId}",
                    'purchase_date'     => '2024-03-01',
                    'purchase_price'    => 85000000,
                    'warranty_expiry'   => '2029-03-01',
                    'description'       => "String Inverter {$invId}, Kapasitas: {$invKwp} kWp ({$invStrCount} strings, cable: {$cable})",
                    'transformer_block' => $zone,
                    'string_number'     => $invNo,
                    'module_slot'       => null,
                    'visual_row'        => null,
                    'visual_col'        => null,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
            }
        }
        fclose($handle);

        $this->command->info("Parsed " . count($pvModulesData) . " PV strings and " . count($invertersData) . " inverters across " . implode(', ', self::VALID_BLOCKS) . ".");

        // 3. Clean and upsert PV String Modules in chunks
        $chunks = array_chunk($pvModulesData, 250);
        foreach ($chunks as $chunk) {
            Asset::upsert(
                $chunk,
                ['asset_code'],
                [
                    'location_id', 'name', 'category', 'location', 'status',
                    'brand', 'model', 'serial_number', 'purchase_date', 'purchase_price',
                    'warranty_expiry', 'description', 'transformer_block',
                    'string_number', 'module_slot', 'visual_row', 'visual_col', 'updated_at'
                ]
            );
        }
        $this->command->info("✓ Upserted " . count($pvModulesData) . " PV Module strings.");

        // 4. Clean and upsert Inverters
        Asset::upsert(
            array_values($invertersData),
            ['asset_code'],
            [
                'location_id', 'name', 'category', 'location', 'status',
                'brand', 'model', 'serial_number', 'purchase_date', 'purchase_price',
                'warranty_expiry', 'description', 'transformer_block',
                'string_number', 'visual_row', 'visual_col', 'updated_at'
            ]
        );
        $this->command->info("✓ Upserted " . count($invertersData) . " Inverters.");

        // 5. Seed Transformers & Metering panels for T01, T02, T04, T05
        $supportingAssets = [];
        foreach ($zoneStats as $zone => $zInfo) {
            $supportingAssets[] = [
                'asset_code'        => "LP-TR-{$zone}",
                'location_id'       => $locationId,
                'name'              => "Transformer {$zone}",
                'category'          => 'Transformer',
                'location'          => "Gardu {$zone}",
                'status'            => 'active',
                'brand'             => 'Schneider Electric',
                'model'             => 'ONAN 5000 kVA 20kV/800V',
                'serial_number'     => "TR-LP-{$zone}-001",
                'purchase_date'     => '2024-02-01',
                'purchase_price'    => 320000000,
                'warranty_expiry'   => '2034-02-01',
                'description'       => "Trafo distribusi {$zone}, kapasitas zona: {$zInfo['capacity_kwp']} kWp",
                'transformer_block' => $zone,
                'string_number'     => null,
                'module_slot'       => null,
                'visual_row'        => null,
                'visual_col'        => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];

            $supportingAssets[] = [
                'asset_code'        => "LP-MET-{$zone}",
                'location_id'       => $locationId,
                'name'              => "Kwh Meter {$zone}",
                'category'          => 'Metering',
                'location'          => "Panel Metering {$zone}",
                'status'            => 'active',
                'brand'             => 'EDMI',
                'model'             => 'Mk10E Kelas 0.5S',
                'serial_number'     => "MET-LP-{$zone}-001",
                'purchase_date'     => '2024-02-15',
                'purchase_price'    => 28000000,
                'warranty_expiry'   => '2029-02-15',
                'description'       => "Kwh meter produksi blok {$zone}",
                'transformer_block' => $zone,
                'string_number'     => null,
                'module_slot'       => null,
                'visual_row'        => null,
                'visual_col'        => null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];

            // 6. Ensure PvMap has a published record for each transformer block
            PvMap::updateOrCreate(
                [
                    'location_id'       => $locationId,
                    'transformer_block' => $zone,
                ],
                [
                    'map_status' => 'published',
                    'map_data'   => $modulesPerBlockForMap[$zone] ?? [],
                ]
            );
        }

        Asset::upsert(
            $supportingAssets,
            ['asset_code'],
            [
                'location_id', 'name', 'category', 'location', 'status',
                'brand', 'model', 'serial_number', 'purchase_date', 'purchase_price',
                'warranty_expiry', 'description', 'transformer_block',
                'visual_row', 'visual_col', 'updated_at'
            ]
        );
        $this->command->info("✓ Upserted " . count($supportingAssets) . " Transformers & Metering panels.");
        $this->command->info("✓ Published PV Maps for valid blocks: " . implode(', ', array_keys($zoneStats)));
    }
}
