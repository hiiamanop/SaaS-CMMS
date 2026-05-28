<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Location;
use Illuminate\Database\Seeder;

class LestariPertiwiSeeder extends Seeder
{
    public function run(): void
    {
        $location = Location::create([
            'name'         => 'Lestari Pertiwi',
            'capacity_mwp' => 0.137,
            'address'      => 'Jl. Lestari Pertiwi No. 1, Area PLTS',
            'is_active'    => true,
        ]);

        $this->seedPVModules($location->id);
        $this->seedSupportingAssets($location->id);
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

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 5) {
                $rows[] = [
                    'block'      => trim($row[0]),
                    'string'     => (int) $row[1],
                    'slot'       => (int) $row[2],
                    'visual_row' => (int) $row[3],
                    'visual_col' => (int) $row[4],
                ];
            }
        }
        fclose($handle);

        foreach ($rows as $r) {
            $strPad  = str_pad($r['string'], 2, '0', STR_PAD_LEFT);
            $slotPad = str_pad($r['slot'], 2, '0', STR_PAD_LEFT);
            $code    = "{$r['block']}-N{$strPad}-S{$slotPad}";

            Asset::create([
                'asset_code'        => $code,
                'location_id'       => $locationId,
                'name'              => "PV Module {$code}",
                'category'          => 'PV Module',
                'location'          => "Ground Array {$r['block']}",
                'status'            => 'active',
                'brand'             => 'Jinko Solar',
                'model'             => 'JKM550M-72HL4-V',
                'serial_number'     => "SN-{$code}",
                'purchase_date'     => '2024-03-01',
                'purchase_price'    => 4200000,
                'warranty_expiry'   => '2049-03-01',
                'description'       => "PV module string N{$strPad}, slot S{$slotPad}, blok {$r['block']}",
                'transformer_block' => $r['block'],
                'string_number'     => $r['string'],
                'module_slot'       => $r['slot'],
                'visual_row'        => $r['visual_row'],
                'visual_col'        => $r['visual_col'],
            ]);
        }

        $count = count($rows);
        $this->command->info("  Seeded PV modules: {$count}");
    }

    private function seedSupportingAssets(int $locationId): void
    {
        $assets = [
            [
                'asset_code'     => 'LP-INV-T01',
                'name'           => 'Inverter T01',
                'category'       => 'Inverter',
                'location'       => 'Ruang Inverter T01',
                'status'         => 'active',
                'brand'          => 'Huawei',
                'model'          => 'SUN2000-36KTL-M3',
                'serial_number'  => 'INV-LP-T01-001',
                'purchase_date'  => '2024-03-01',
                'purchase_price' => 85000000,
                'warranty_expiry'=> '2029-03-01',
                'description'    => 'String inverter 36kW untuk transformer block T01',
            ],
            [
                'asset_code'     => 'LP-TR-T01',
                'name'           => 'Transformer T01',
                'category'       => 'Transformer',
                'location'       => 'Gardu T01',
                'status'         => 'active',
                'brand'          => 'Schneider Electric',
                'model'          => 'ONAN 160 kVA 20kV/380V',
                'serial_number'  => 'TR-LP-T01-001',
                'purchase_date'  => '2024-02-01',
                'purchase_price' => 320000000,
                'warranty_expiry'=> '2034-02-01',
                'description'    => 'Trafo distribusi T01, 160kVA, 20kV/380V',
            ],
            [
                'asset_code'     => 'LP-MET-T01',
                'name'           => 'Kwh Meter T01',
                'category'       => 'Metering',
                'location'       => 'Panel Metering T01',
                'status'         => 'active',
                'brand'          => 'Schneider Electric',
                'model'          => 'PM5350',
                'serial_number'  => 'MET-LP-T01-001',
                'purchase_date'  => '2024-03-01',
                'purchase_price' => 12000000,
                'warranty_expiry'=> '2029-03-01',
                'description'    => 'Power meter digital untuk monitoring T01',
            ],
        ];

        foreach ($assets as $asset) {
            Asset::create(array_merge($asset, ['location_id' => $locationId]));
        }

        $count = count($assets);
        $this->command->info("  Seeded supporting assets: {$count}");
    }
}
