<?php

namespace Database\Seeders;

use App\Models\SparePart;
use Illuminate\Database\Seeder;

class SparePartSeeder extends Seeder
{
    public function run(): void
    {
        $parts = [
            // Konektor & Kabel DC
            ['part_code' => 'SP-DC-001', 'name' => 'MC4 Connector Male',            'category' => 'Konektor DC', 'unit' => 'pcs',  'qty_actual' => 50,  'qty_minimum' => 20, 'unit_price' => 25000,    'supplier' => 'Stäubli Indonesia',     'location' => 'Gudang A-1'],
            ['part_code' => 'SP-DC-002', 'name' => 'MC4 Connector Female',           'category' => 'Konektor DC', 'unit' => 'pcs',  'qty_actual' => 50,  'qty_minimum' => 20, 'unit_price' => 25000,    'supplier' => 'Stäubli Indonesia',     'location' => 'Gudang A-1'],
            ['part_code' => 'SP-DC-003', 'name' => 'Kabel DC 4mm² (per meter)',      'category' => 'Kabel',       'unit' => 'meter', 'qty_actual' => 200, 'qty_minimum' => 50, 'unit_price' => 18000,    'supplier' => 'Supreme Cable',         'location' => 'Gudang A-2'],
            ['part_code' => 'SP-DC-004', 'name' => 'Kabel DC 6mm² (per meter)',      'category' => 'Kabel',       'unit' => 'meter', 'qty_actual' => 100, 'qty_minimum' => 30, 'unit_price' => 26000,    'supplier' => 'Supreme Cable',         'location' => 'Gudang A-2'],
            ['part_code' => 'SP-DC-005', 'name' => 'Fuse DC 10A 1000V',              'category' => 'Proteksi',    'unit' => 'pcs',  'qty_actual' => 30,  'qty_minimum' => 20, 'unit_price' => 35000,    'supplier' => 'Mersen Indonesia',      'location' => 'Gudang B-1'],
            ['part_code' => 'SP-DC-006', 'name' => 'Fuse DC 15A 1000V',              'category' => 'Proteksi',    'unit' => 'pcs',  'qty_actual' => 20,  'qty_minimum' => 10, 'unit_price' => 40000,    'supplier' => 'Mersen Indonesia',      'location' => 'Gudang B-1'],
            ['part_code' => 'SP-DC-007', 'name' => 'SPD DC Type II (1000V)',         'category' => 'Proteksi',    'unit' => 'pcs',  'qty_actual' => 4,   'qty_minimum' => 2,  'unit_price' => 850000,   'supplier' => 'Dehn Indonesia',        'location' => 'Gudang B-1'],
            ['part_code' => 'SP-DC-008', 'name' => 'String Combiner Box 8-in-1',     'category' => 'Komponen DC', 'unit' => 'pcs',  'qty_actual' => 1,   'qty_minimum' => 1,  'unit_price' => 4500000,  'supplier' => 'Schukat Indonesia',     'location' => 'Gudang B-2'],

            // Komponen Inverter
            ['part_code' => 'SP-INV-001', 'name' => 'Fan Inverter SMA 100kW',        'category' => 'Inverter',    'unit' => 'pcs',  'qty_actual' => 3,   'qty_minimum' => 2,  'unit_price' => 650000,   'supplier' => 'SMA Service Center',    'location' => 'Gudang C-1'],
            ['part_code' => 'SP-INV-002', 'name' => 'Filter Udara Inverter',          'category' => 'Inverter',    'unit' => 'pcs',  'qty_actual' => 6,   'qty_minimum' => 3,  'unit_price' => 120000,   'supplier' => 'SMA Service Center',    'location' => 'Gudang C-1'],
            ['part_code' => 'SP-INV-003', 'name' => 'Kapasitor DC Link 1000V/1000µF','category' => 'Inverter',    'unit' => 'pcs',  'qty_actual' => 4,   'qty_minimum' => 2,  'unit_price' => 1200000,  'supplier' => 'Vishay Indonesia',      'location' => 'Gudang C-1'],
            ['part_code' => 'SP-INV-004', 'name' => 'Terminal Block Inverter (set)',  'category' => 'Inverter',    'unit' => 'set',  'qty_actual' => 5,   'qty_minimum' => 3,  'unit_price' => 280000,   'supplier' => 'Phoenix Contact',       'location' => 'Gudang C-1'],

            // Panel LV
            ['part_code' => 'SP-PLV-001', 'name' => 'MCCB 3P 250A',                  'category' => 'Panel LV',    'unit' => 'pcs',  'qty_actual' => 2,   'qty_minimum' => 1,  'unit_price' => 3500000,  'supplier' => 'Schneider Electric',    'location' => 'Gudang D-1'],
            ['part_code' => 'SP-PLV-002', 'name' => 'MCB 1P 16A',                    'category' => 'Panel LV',    'unit' => 'pcs',  'qty_actual' => 10,  'qty_minimum' => 5,  'unit_price' => 85000,    'supplier' => 'Schneider Electric',    'location' => 'Gudang D-1'],
            ['part_code' => 'SP-PLV-003', 'name' => 'SPD AC Type II 40kA',           'category' => 'Panel LV',    'unit' => 'pcs',  'qty_actual' => 4,   'qty_minimum' => 2,  'unit_price' => 950000,   'supplier' => 'Dehn Indonesia',        'location' => 'Gudang D-1'],
            ['part_code' => 'SP-PLV-004', 'name' => 'Kontaktor AC 80A',              'category' => 'Panel LV',    'unit' => 'pcs',  'qty_actual' => 2,   'qty_minimum' => 1,  'unit_price' => 1250000,  'supplier' => 'Schneider Electric',    'location' => 'Gudang D-1'],
            ['part_code' => 'SP-PLV-005', 'name' => 'Baterai UPS 12V 9Ah',          'category' => 'Panel LV',    'unit' => 'pcs',  'qty_actual' => 8,   'qty_minimum' => 4,  'unit_price' => 185000,   'supplier' => 'Yuasa Battery',         'location' => 'Gudang D-2'],

            // Transformer
            ['part_code' => 'SP-TR-001',  'name' => 'Minyak Trafo (20L)',             'category' => 'Transformer', 'unit' => 'drum', 'qty_actual' => 3,   'qty_minimum' => 2,  'unit_price' => 1800000,  'supplier' => 'Pertamina Lubricants',  'location' => 'Oil Storage'],
            ['part_code' => 'SP-TR-002',  'name' => 'Gasket Karet Trafo (set)',       'category' => 'Transformer', 'unit' => 'set',  'qty_actual' => 2,   'qty_minimum' => 1,  'unit_price' => 650000,   'supplier' => 'ABB Service Center',    'location' => 'Gudang E-1'],
            ['part_code' => 'SP-TR-003',  'name' => 'Bushing Trafo 20kV',            'category' => 'Transformer', 'unit' => 'pcs',  'qty_actual' => 1,   'qty_minimum' => 1,  'unit_price' => 4200000,  'supplier' => 'ABB Service Center',    'location' => 'Gudang E-1'],

            // Umum PLTS
            ['part_code' => 'SP-GEN-001', 'name' => 'Grease Kontak Listrik (400g)',  'category' => 'Pelumas',     'unit' => 'kaleng','qty_actual'=> 10,   'qty_minimum' => 4,  'unit_price' => 120000,   'supplier' => 'CRC Industries',        'location' => 'Gudang A-3'],
            ['part_code' => 'SP-GEN-002', 'name' => 'Isolasi Listrik 3M (roll)',     'category' => 'Consumable',  'unit' => 'roll', 'qty_actual' => 20,  'qty_minimum' => 10, 'unit_price' => 35000,    'supplier' => '3M Indonesia',          'location' => 'Gudang A-3'],
            ['part_code' => 'SP-GEN-003', 'name' => 'Cable Tie UV Resistant 30cm',   'category' => 'Consumable',  'unit' => 'pack', 'qty_actual' => 15,  'qty_minimum' => 5,  'unit_price' => 45000,    'supplier' => 'Hellermann Tyton',      'location' => 'Gudang A-3'],
            ['part_code' => 'SP-GEN-004', 'name' => 'Baut Stainless M8x20 (pak 50)', 'category' => 'Fastener',   'unit' => 'pack', 'qty_actual' => 8,   'qty_minimum' => 3,  'unit_price' => 65000,    'supplier' => 'Local Supplier',        'location' => 'Gudang A-3'],
            ['part_code' => 'SP-GEN-005', 'name' => 'Kabel Grounding 16mm² (meter)', 'category' => 'Kabel',      'unit' => 'meter', 'qty_actual' => 50,  'qty_minimum' => 20, 'unit_price' => 55000,    'supplier' => 'Supreme Cable',         'location' => 'Gudang A-2'],
        ];

        foreach ($parts as $part) {
            SparePart::create($part);
        }
    }
}
