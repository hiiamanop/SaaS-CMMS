<?php

namespace Database\Seeders;

use App\Models\SparePart;
use Illuminate\Database\Seeder;

class SparePartSeeder extends Seeder
{
    public function run(): void
    {
        $parts = [
            ['part_code' => 'SP-001', 'name' => 'Oil Filter (AC)', 'category' => 'Filter', 'unit' => 'pcs', 'qty_actual' => 3, 'qty_minimum' => 5, 'unit_price' => 150000, 'supplier' => 'Indoparts', 'location' => 'Warehouse A-1'],
            ['part_code' => 'SP-002', 'name' => 'V-Belt A68', 'category' => 'Belt', 'unit' => 'pcs', 'qty_actual' => 8, 'qty_minimum' => 4, 'unit_price' => 75000, 'supplier' => 'Gates Indonesia', 'location' => 'Warehouse A-2'],
            ['part_code' => 'SP-003', 'name' => 'Bearing 6205 ZZ', 'category' => 'Bearing', 'unit' => 'pcs', 'qty_actual' => 2, 'qty_minimum' => 6, 'unit_price' => 45000, 'supplier' => 'SKF Indonesia', 'location' => 'Warehouse B-1'],
            ['part_code' => 'SP-004', 'name' => 'Hydraulic Oil VG46 (20L)', 'category' => 'Lubricant', 'unit' => 'drum', 'qty_actual' => 3, 'qty_minimum' => 2, 'unit_price' => 850000, 'supplier' => 'Pertamina Lubricants', 'location' => 'Oil Storage'],
            ['part_code' => 'SP-005', 'name' => 'Air Filter Element', 'category' => 'Filter', 'unit' => 'pcs', 'qty_actual' => 4, 'qty_minimum' => 3, 'unit_price' => 280000, 'supplier' => 'Donaldson', 'location' => 'Warehouse A-1'],
            ['part_code' => 'SP-006', 'name' => 'Fuse 10A Glass', 'category' => 'Electrical', 'unit' => 'pcs', 'qty_actual' => 1, 'qty_minimum' => 10, 'unit_price' => 5000, 'supplier' => 'Schneider Electric', 'location' => 'Electrical Room'],
            ['part_code' => 'SP-007', 'name' => 'Compressor Oil (1L)', 'category' => 'Lubricant', 'unit' => 'bottle', 'qty_actual' => 6, 'qty_minimum' => 4, 'unit_price' => 120000, 'supplier' => 'Atlas Copco', 'location' => 'Oil Storage'],
            ['part_code' => 'SP-008', 'name' => 'Conveyor Belt (per meter)', 'category' => 'Belt', 'unit' => 'meter', 'qty_actual' => 5, 'qty_minimum' => 10, 'unit_price' => 320000, 'supplier' => 'Continental', 'location' => 'Warehouse C-1'],
            ['part_code' => 'SP-009', 'name' => 'Pneumatic Seal Kit', 'category' => 'Seal', 'unit' => 'set', 'qty_actual' => 2, 'qty_minimum' => 3, 'unit_price' => 180000, 'supplier' => 'Parker Hannifin', 'location' => 'Warehouse B-2'],
            ['part_code' => 'SP-010', 'name' => 'Circuit Breaker 16A', 'category' => 'Electrical', 'unit' => 'pcs', 'qty_actual' => 0, 'qty_minimum' => 5, 'unit_price' => 95000, 'supplier' => 'ABB Indonesia', 'location' => 'Electrical Room'],
            ['part_code' => 'SP-011', 'name' => 'Gasket Set Engine', 'category' => 'Seal', 'unit' => 'set', 'qty_actual' => 1, 'qty_minimum' => 2, 'unit_price' => 450000, 'supplier' => 'Perkins', 'location' => 'Warehouse B-2'],
            ['part_code' => 'SP-012', 'name' => 'Grease (500g)', 'category' => 'Lubricant', 'unit' => 'can', 'qty_actual' => 12, 'qty_minimum' => 5, 'unit_price' => 65000, 'supplier' => 'SKF Indonesia', 'location' => 'Oil Storage'],
            ['part_code' => 'SP-013', 'name' => 'Roller Chain 50-2 (per foot)', 'category' => 'Chain', 'unit' => 'foot', 'qty_actual' => 10, 'qty_minimum' => 20, 'unit_price' => 45000, 'supplier' => 'Tsubaki', 'location' => 'Warehouse A-2'],
            ['part_code' => 'SP-014', 'name' => 'PLC Battery CR2032', 'category' => 'Electrical', 'unit' => 'pcs', 'qty_actual' => 4, 'qty_minimum' => 4, 'unit_price' => 25000, 'supplier' => 'Panasonic', 'location' => 'Electrical Room'],
            ['part_code' => 'SP-015', 'name' => 'Cooling Fan Motor 0.5HP', 'category' => 'Motor', 'unit' => 'pcs', 'qty_actual' => 1, 'qty_minimum' => 2, 'unit_price' => 850000, 'supplier' => 'Siemens', 'location' => 'Warehouse D-1'],
            ['part_code' => 'SP-016', 'name' => 'Hydraulic Hose 1/2 inch', 'category' => 'Hose', 'unit' => 'meter', 'qty_actual' => 15, 'qty_minimum' => 10, 'unit_price' => 75000, 'supplier' => 'Parker Hannifin', 'location' => 'Warehouse C-2'],
            ['part_code' => 'SP-017', 'name' => 'Contactor 18A', 'category' => 'Electrical', 'unit' => 'pcs', 'qty_actual' => 2, 'qty_minimum' => 3, 'unit_price' => 285000, 'supplier' => 'Schneider Electric', 'location' => 'Electrical Room'],
            ['part_code' => 'SP-018', 'name' => 'Bolt M10x50 SS (pack 20)', 'category' => 'Fastener', 'unit' => 'pack', 'qty_actual' => 5, 'qty_minimum' => 2, 'unit_price' => 45000, 'supplier' => 'Local Supplier', 'location' => 'Warehouse A-3'],
            ['part_code' => 'SP-019', 'name' => 'Gearbox Oil GL-4 (20L)', 'category' => 'Lubricant', 'unit' => 'drum', 'qty_actual' => 1, 'qty_minimum' => 2, 'unit_price' => 950000, 'supplier' => 'Pertamina Lubricants', 'location' => 'Oil Storage'],
            ['part_code' => 'SP-020', 'name' => 'Encoder Incremental 1000PPR', 'category' => 'Sensor', 'unit' => 'pcs', 'qty_actual' => 0, 'qty_minimum' => 1, 'unit_price' => 1250000, 'supplier' => 'Omron Indonesia', 'location' => 'Warehouse D-2'],
            ['part_code' => 'SP-021', 'name' => 'Safety Valve 1/2 inch', 'category' => 'Valve', 'unit' => 'pcs', 'qty_actual' => 3, 'qty_minimum' => 2, 'unit_price' => 380000, 'supplier' => 'Swagelok', 'location' => 'Warehouse B-3'],
            ['part_code' => 'SP-022', 'name' => 'Coupling Jaw Type 65', 'category' => 'Coupling', 'unit' => 'pcs', 'qty_actual' => 2, 'qty_minimum' => 2, 'unit_price' => 225000, 'supplier' => 'Rexnord', 'location' => 'Warehouse A-2'],
            ['part_code' => 'SP-023', 'name' => 'Pressure Gauge 0-10 bar', 'category' => 'Instrument', 'unit' => 'pcs', 'qty_actual' => 4, 'qty_minimum' => 2, 'unit_price' => 185000, 'supplier' => 'Wika', 'location' => 'Warehouse D-3'],
            ['part_code' => 'SP-024', 'name' => 'Motor Capacitor 35uF', 'category' => 'Electrical', 'unit' => 'pcs', 'qty_actual' => 1, 'qty_minimum' => 4, 'unit_price' => 75000, 'supplier' => 'Local Supplier', 'location' => 'Electrical Room'],
            ['part_code' => 'SP-025', 'name' => 'Coolant Filter', 'category' => 'Filter', 'unit' => 'pcs', 'qty_actual' => 5, 'qty_minimum' => 3, 'unit_price' => 135000, 'supplier' => 'Fleetguard', 'location' => 'Warehouse A-1'],
            ['part_code' => 'SP-026', 'name' => 'O-Ring Set (assorted)', 'category' => 'Seal', 'unit' => 'set', 'qty_actual' => 3, 'qty_minimum' => 2, 'unit_price' => 95000, 'supplier' => 'Parker Hannifin', 'location' => 'Warehouse B-2'],
            ['part_code' => 'SP-027', 'name' => 'Limit Switch Micro', 'category' => 'Sensor', 'unit' => 'pcs', 'qty_actual' => 2, 'qty_minimum' => 4, 'unit_price' => 65000, 'supplier' => 'Omron Indonesia', 'location' => 'Electrical Room'],
            ['part_code' => 'SP-028', 'name' => 'Welding Wire ER70S-6 (5kg)', 'category' => 'Consumable', 'unit' => 'roll', 'qty_actual' => 8, 'qty_minimum' => 3, 'unit_price' => 145000, 'supplier' => 'Esab', 'location' => 'Warehouse C-3'],
            ['part_code' => 'SP-029', 'name' => 'Transformer 24V 2A', 'category' => 'Electrical', 'unit' => 'pcs', 'qty_actual' => 1, 'qty_minimum' => 2, 'unit_price' => 185000, 'supplier' => 'Schneider Electric', 'location' => 'Electrical Room'],
            ['part_code' => 'SP-030', 'name' => 'Drill Bit Set HSS (13pcs)', 'category' => 'Tool', 'unit' => 'set', 'qty_actual' => 4, 'qty_minimum' => 2, 'unit_price' => 125000, 'supplier' => 'Bosch', 'location' => 'Tool Storage'],
        ];

        foreach ($parts as $part) {
            SparePart::create($part);
        }
    }
}
