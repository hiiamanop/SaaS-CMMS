<?php

namespace Database\Seeders;

use App\Models\Asset;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            // PV Module
            [
                'asset_code'      => 'PLTS-PV-001',
                'name'            => 'PV Module Area 01',
                'category'        => 'PV Module',
                'location'        => 'Rooftop Blok A',
                'status'          => 'active',
                'brand'           => 'Jinko Solar',
                'model'           => 'JKM550M-72HL4-V',
                'serial_number'   => 'PV-2024-001',
                'purchase_date'   => '2024-03-01',
                'purchase_price'  => 850000000,
                'warranty_expiry' => '2049-03-01',
                'description'     => 'Array panel surya 100kWp, 182 modul, Rooftop Blok A',
            ],
            [
                'asset_code'      => 'PLTS-PV-002',
                'name'            => 'PV Module Area 02',
                'category'        => 'PV Module',
                'location'        => 'Rooftop Blok B',
                'status'          => 'active',
                'brand'           => 'Jinko Solar',
                'model'           => 'JKM550M-72HL4-V',
                'serial_number'   => 'PV-2024-002',
                'purchase_date'   => '2024-03-01',
                'purchase_price'  => 850000000,
                'warranty_expiry' => '2049-03-01',
                'description'     => 'Array panel surya 100kWp, 182 modul, Rooftop Blok B',
            ],
            [
                'asset_code'      => 'PLTS-PV-003',
                'name'            => 'PV Module Area 03',
                'category'        => 'PV Module',
                'location'        => 'Ground Mount Barat',
                'status'          => 'active',
                'brand'           => 'Canadian Solar',
                'model'           => 'CS6W-545MS',
                'serial_number'   => 'PV-2024-003',
                'purchase_date'   => '2024-04-01',
                'purchase_price'  => 920000000,
                'warranty_expiry' => '2049-04-01',
                'description'     => 'Array panel surya 120kWp, ground mount area barat',
            ],

            // Inverter
            [
                'asset_code'      => 'PLTS-INV-001',
                'name'            => 'Inverter 01',
                'category'        => 'Inverter',
                'location'        => 'Ruang Inverter Blok A',
                'status'          => 'active',
                'brand'           => 'SMA',
                'model'           => 'Sunny Tripower 100kW',
                'serial_number'   => 'INV-2024-001',
                'purchase_date'   => '2024-03-01',
                'purchase_price'  => 450000000,
                'warranty_expiry' => '2029-03-01',
                'description'     => 'String inverter 100kW untuk array PV Area 01',
            ],
            [
                'asset_code'      => 'PLTS-INV-002',
                'name'            => 'Inverter 02',
                'category'        => 'Inverter',
                'location'        => 'Ruang Inverter Blok B',
                'status'          => 'active',
                'brand'           => 'SMA',
                'model'           => 'Sunny Tripower 100kW',
                'serial_number'   => 'INV-2024-002',
                'purchase_date'   => '2024-03-01',
                'purchase_price'  => 450000000,
                'warranty_expiry' => '2029-03-01',
                'description'     => 'String inverter 100kW untuk array PV Area 02',
            ],
            [
                'asset_code'      => 'PLTS-INV-003',
                'name'            => 'Inverter 03',
                'category'        => 'Inverter',
                'location'        => 'Ruang Inverter Ground Mount',
                'status'          => 'active',
                'brand'           => 'Huawei',
                'model'           => 'SUN2000-120KTL-M1',
                'serial_number'   => 'INV-2024-003',
                'purchase_date'   => '2024-04-01',
                'purchase_price'  => 520000000,
                'warranty_expiry' => '2029-04-01',
                'description'     => 'String inverter 120kW untuk area ground mount barat',
            ],

            // Panel LV
            [
                'asset_code'      => 'PLTS-PLV-001',
                'name'            => 'Panel LV 01',
                'category'        => 'Panel LV',
                'location'        => 'Ruang Panel Blok A',
                'status'          => 'active',
                'brand'           => 'Schneider Electric',
                'model'           => 'Prisma G 400A',
                'serial_number'   => 'PLV-2024-001',
                'purchase_date'   => '2024-03-01',
                'purchase_price'  => 185000000,
                'warranty_expiry' => '2029-03-01',
                'description'     => 'Panel distribusi LV utama Blok A, 400A, MCCB + SPD + UPS',
            ],
            [
                'asset_code'      => 'PLTS-PLV-002',
                'name'            => 'Panel LV 02',
                'category'        => 'Panel LV',
                'location'        => 'Ruang Panel Blok B',
                'status'          => 'active',
                'brand'           => 'Schneider Electric',
                'model'           => 'Prisma G 400A',
                'serial_number'   => 'PLV-2024-002',
                'purchase_date'   => '2024-03-01',
                'purchase_price'  => 185000000,
                'warranty_expiry' => '2029-03-01',
                'description'     => 'Panel distribusi LV utama Blok B, 400A, MCCB + SPD + UPS',
            ],

            // Transformer
            [
                'asset_code'      => 'PLTS-TR-001',
                'name'            => 'Transformer 01',
                'category'        => 'Transformer',
                'location'        => 'Gardu Induk Blok A',
                'status'          => 'active',
                'brand'           => 'ABB',
                'model'           => 'ONAN 250 kVA 20kV/400V',
                'serial_number'   => 'TR-2024-001',
                'purchase_date'   => '2024-02-01',
                'purchase_price'  => 380000000,
                'warranty_expiry' => '2029-02-01',
                'description'     => 'Trafo step-down 250kVA, 20kV/400V, oil immersed ONAN, Gardu Blok A',
            ],
            [
                'asset_code'      => 'PLTS-TR-002',
                'name'            => 'Transformer 02',
                'category'        => 'Transformer',
                'location'        => 'Gardu Induk Blok B',
                'status'          => 'active',
                'brand'           => 'ABB',
                'model'           => 'ONAN 250 kVA 20kV/400V',
                'serial_number'   => 'TR-2024-002',
                'purchase_date'   => '2024-02-01',
                'purchase_price'  => 380000000,
                'warranty_expiry' => '2029-02-01',
                'description'     => 'Trafo step-down 250kVA, 20kV/400V, oil immersed ONAN, Gardu Blok B',
            ],
        ];

        foreach ($assets as $asset) {
            Asset::create($asset);
        }
    }
}
