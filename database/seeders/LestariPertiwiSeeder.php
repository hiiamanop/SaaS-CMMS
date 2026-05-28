<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Location;
use Illuminate\Database\Seeder;

class LestariPertiwiSeeder extends Seeder
{
    public function run(): void
    {
        $location = Location::firstOrCreate(
            ['name' => 'Lestari Pertiwi'],
            [
                'capacity_mwp' => 0.137,
                'address'      => 'Jl. Lestari Pertiwi No. 1, Area PLTS',
                'is_active'    => true,
            ]
        );

        $this->seedPVModules($location->id);
        $this->seedSupportingAssets($location->id);
        $this->seedStringInverters($location->id);
    }

    private function seedPVModules(int $locationId): void
    {
        $csvPath = base_path('docs/T01-249_PV_Layout.csv');

        if (!file_exists($csvPath)) {
            $this->command->warn('CSV not found: docs/T01-249_PV_Layout.csv — PV modules skipped.');
            return;
        }

        $handle = fopen($csvPath, 'r');
        fgetcsv($handle); // skip header

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) continue;

            [$block, $string, $slot, $visualRow, $visualCol] = $row;
            $block  = trim($block);
            $string = (int) $string;
            $slot   = (int) $slot;

            $strPad  = str_pad($string, 2, '0', STR_PAD_LEFT);
            $slotPad = str_pad($slot,   2, '0', STR_PAD_LEFT);
            $code    = "{$block}-INV{$strPad}-S{$slotPad}";

            Asset::updateOrCreate(
                [
                    'transformer_block' => $block,
                    'string_number'     => $string,
                    'module_slot'       => $slot,
                ],
                [
                    'asset_code'      => $code,
                    'location_id'     => $locationId,
                    'name'            => "PV Module {$code}",
                    'category'        => 'PV Module',
                    'location'        => "Ground Array {$block}",
                    'status'          => 'active',
                    'brand'           => 'Jinko Solar',
                    'model'           => 'JKM550M-72HL4-V',
                    'serial_number'   => "SN-{$code}",
                    'purchase_date'   => '2024-03-01',
                    'purchase_price'  => 4200000,
                    'warranty_expiry' => '2049-03-01',
                    'description'     => "PV module INV{$strPad}-S{$slotPad}, blok {$block}",
                    'visual_row'      => (int) $visualRow,
                    'visual_col'      => (int) $visualCol,
                ]
            );
            $count++;
        }

        fclose($handle);
        $this->command->info("  PV modules T01: {$count}");
    }

    private function seedSupportingAssets(int $locationId): void
    {
        $assets = [
            [
                'asset_code'        => 'LP-INV-T01',
                'name'              => 'Inverter T01',
                'category'          => 'Inverter',
                'location'          => 'Ruang Inverter T01',
                'status'            => 'active',
                'brand'             => 'Huawei',
                'model'             => 'SUN2000-36KTL-M3',
                'serial_number'     => 'INV-LP-T01-001',
                'purchase_date'     => '2024-03-01',
                'purchase_price'    => 85000000,
                'warranty_expiry'   => '2029-03-01',
                'description'       => 'String inverter 36kW untuk transformer block T01',
                'transformer_block' => 'T01',
            ],
            [
                'asset_code'        => 'LP-TR-T01',
                'name'              => 'Transformer T01',
                'category'          => 'Transformer',
                'location'          => 'Gardu T01',
                'status'            => 'active',
                'brand'             => 'Schneider Electric',
                'model'             => 'ONAN 160 kVA 20kV/380V',
                'serial_number'     => 'TR-LP-T01-001',
                'purchase_date'     => '2024-02-01',
                'purchase_price'    => 320000000,
                'warranty_expiry'   => '2034-02-01',
                'description'       => 'Trafo distribusi T01, 160kVA, 20kV/380V',
                'transformer_block' => 'T01',
                'visual_row'        => 15,
                'visual_col'        => 18,
            ],
            [
                'asset_code'        => 'LP-MET-T01',
                'name'              => 'Kwh Meter T01',
                'category'          => 'Metering',
                'location'          => 'Panel Metering T01',
                'status'            => 'active',
                'brand'             => 'Schneider Electric',
                'model'             => 'PM5350',
                'serial_number'     => 'MET-LP-T01-001',
                'purchase_date'     => '2024-03-01',
                'purchase_price'    => 12000000,
                'warranty_expiry'   => '2029-03-01',
                'description'       => 'Power meter digital untuk monitoring T01',
                'transformer_block' => 'T01',
            ],
        ];

        foreach ($assets as $asset) {
            Asset::updateOrCreate(
                ['asset_code' => $asset['asset_code']],
                array_merge($asset, ['location_id' => $locationId])
            );
        }

        $this->command->info('  Supporting assets T01: ' . count($assets));
    }

    private function seedStringInverters(int $locationId): void
    {
        $inverters = [
            ['code' => 'T01-INV01', 'row' => 4,  'col' => 10],
            ['code' => 'T01-INV02', 'row' => 2,  'col' => 10],
            ['code' => 'T01-INV03', 'row' => 5,  'col' => 17],
            ['code' => 'T01-INV04', 'row' => 2,  'col' => 17],
            ['code' => 'T01-INV05', 'row' => 6,  'col' => 10],
            ['code' => 'T01-INV06', 'row' => 9,  'col' => 17],
            ['code' => 'T01-INV07', 'row' => 7,  'col' => 17],
            ['code' => 'T01-INV08', 'row' => 8,  'col' => 10],
            ['code' => 'T01-INV09', 'row' => 10, 'col' => 17],
            ['code' => 'T01-INV10', 'row' => 10, 'col' => 10],
            ['code' => 'T01-INV11', 'row' => 12, 'col' => 17],
            ['code' => 'T01-INV12', 'row' => 13, 'col' => 17],
        ];

        foreach ($inverters as $inv) {
            Asset::updateOrCreate(
                ['asset_code' => $inv['code']],
                [
                    'location_id'       => $locationId,
                    'name'              => 'Inverter ' . $inv['code'],
                    'category'          => 'Inverter',
                    'location'          => 'Area PLTS T01',
                    'status'            => 'active',
                    'brand'             => 'Huawei',
                    'model'             => 'SUN2000-36KTL-M3',
                    'transformer_block' => 'T01',
                    'visual_row'        => $inv['row'],
                    'visual_col'        => $inv['col'],
                ]
            );
        }

        $this->command->info('  String inverters T01: ' . count($inverters));
    }
}
