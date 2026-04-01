<?php

namespace Database\Seeders;

use App\Models\Asset;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['asset_code' => 'AST-001', 'name' => 'Air Compressor Unit A', 'category' => 'Mechanical', 'location' => 'Production Floor 1', 'status' => 'active', 'brand' => 'Atlas Copco', 'model' => 'GA15', 'serial_number' => 'AC-2020-001', 'purchase_date' => '2020-01-15', 'purchase_price' => 45000000, 'warranty_expiry' => '2023-01-15', 'description' => 'Main air compressor for production line A'],
            ['asset_code' => 'AST-002', 'name' => 'CNC Milling Machine', 'category' => 'Machinery', 'location' => 'Workshop 1', 'status' => 'active', 'brand' => 'Haas', 'model' => 'VF-2', 'serial_number' => 'CNC-2019-001', 'purchase_date' => '2019-06-20', 'purchase_price' => 350000000, 'warranty_expiry' => '2022-06-20', 'description' => 'CNC vertical milling machine'],
            ['asset_code' => 'AST-003', 'name' => 'Conveyor Belt System', 'category' => 'Mechanical', 'location' => 'Production Floor 2', 'status' => 'active', 'brand' => 'Dorner', 'model' => '2200', 'serial_number' => 'CB-2021-001', 'purchase_date' => '2021-03-10', 'purchase_price' => 85000000, 'warranty_expiry' => '2024-03-10', 'description' => 'Main conveyor belt for production line B'],
            ['asset_code' => 'AST-004', 'name' => 'Industrial Chiller', 'category' => 'HVAC', 'location' => 'Utility Room', 'status' => 'active', 'brand' => 'Carrier', 'model' => '30XW', 'serial_number' => 'CH-2020-001', 'purchase_date' => '2020-08-05', 'purchase_price' => 120000000, 'warranty_expiry' => '2023-08-05', 'description' => 'Industrial water chiller unit'],
            ['asset_code' => 'AST-005', 'name' => 'Forklift Electric', 'category' => 'Vehicle', 'location' => 'Warehouse', 'status' => 'active', 'brand' => 'Toyota', 'model' => '8FBE20', 'serial_number' => 'FL-2022-001', 'purchase_date' => '2022-01-20', 'purchase_price' => 180000000, 'warranty_expiry' => '2025-01-20', 'description' => 'Electric forklift for warehouse operations'],
            ['asset_code' => 'AST-006', 'name' => 'Generator Set 100KVA', 'category' => 'Electrical', 'location' => 'Power House', 'status' => 'active', 'brand' => 'Perkins', 'model' => 'P88S', 'serial_number' => 'GEN-2019-001', 'purchase_date' => '2019-11-15', 'purchase_price' => 95000000, 'warranty_expiry' => '2022-11-15', 'description' => 'Backup generator set 100KVA'],
            ['asset_code' => 'AST-007', 'name' => 'Water Treatment Plant', 'category' => 'Utility', 'location' => 'Utility Building', 'status' => 'under_maintenance', 'brand' => 'Evoqua', 'model' => 'W100', 'serial_number' => 'WTP-2021-001', 'purchase_date' => '2021-05-10', 'purchase_price' => 250000000, 'warranty_expiry' => '2024-05-10', 'description' => 'Industrial water treatment system'],
            ['asset_code' => 'AST-008', 'name' => 'Overhead Crane 5T', 'category' => 'Machinery', 'location' => 'Assembly Hall', 'status' => 'active', 'brand' => 'Konecranes', 'model' => 'CXT5', 'serial_number' => 'OHC-2020-001', 'purchase_date' => '2020-09-12', 'purchase_price' => 320000000, 'warranty_expiry' => '2023-09-12', 'description' => '5-ton overhead bridge crane'],
            ['asset_code' => 'AST-009', 'name' => 'Hydraulic Press 200T', 'category' => 'Machinery', 'location' => 'Workshop 2', 'status' => 'active', 'brand' => 'Schuler', 'model' => 'HP200', 'serial_number' => 'HP-2018-001', 'purchase_date' => '2018-04-18', 'purchase_price' => 450000000, 'warranty_expiry' => '2021-04-18', 'description' => '200-ton hydraulic press machine'],
            ['asset_code' => 'AST-010', 'name' => 'Industrial Robot Arm', 'category' => 'Automation', 'location' => 'Production Floor 1', 'status' => 'inactive', 'brand' => 'FANUC', 'model' => 'M-10iA', 'serial_number' => 'ROB-2022-001', 'purchase_date' => '2022-07-01', 'purchase_price' => 280000000, 'warranty_expiry' => '2025-07-01', 'description' => 'Articulated robot arm for welding'],
        ];

        foreach ($assets as $asset) {
            Asset::create($asset);
        }
    }
}
