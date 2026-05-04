<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FieldConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            // Assets
            ['module' => 'assets', 'field_name' => 'asset_code', 'label' => 'Asset Code', 'is_required' => false],
            ['module' => 'assets', 'field_name' => 'name', 'label' => 'Name', 'is_required' => true],
            ['module' => 'assets', 'field_name' => 'category', 'label' => 'Category', 'is_required' => true],
            ['module' => 'assets', 'field_name' => 'location_id', 'label' => 'Location', 'is_required' => true],
            ['module' => 'assets', 'field_name' => 'brand', 'label' => 'Brand', 'is_required' => false],
            ['module' => 'assets', 'field_name' => 'model_number', 'label' => 'Model Number', 'is_required' => false],
            ['module' => 'assets', 'field_name' => 'serial_number', 'label' => 'Serial Number', 'is_required' => false],
            ['module' => 'assets', 'field_name' => 'purchase_date', 'label' => 'Purchase Date', 'is_required' => false],
            ['module' => 'assets', 'field_name' => 'warranty_expiry', 'label' => 'Warranty Expiry', 'is_required' => false],
            ['module' => 'assets', 'field_name' => 'description', 'label' => 'Description', 'is_required' => false],

            // Consumables
            ['module' => 'consumables', 'field_name' => 'item_code', 'label' => 'Item Code', 'is_required' => false],
            ['module' => 'consumables', 'field_name' => 'name', 'label' => 'Name', 'is_required' => true],
            ['module' => 'consumables', 'field_name' => 'category', 'label' => 'Category', 'is_required' => false],
            ['module' => 'consumables', 'field_name' => 'stock', 'label' => 'Current Stock', 'is_required' => true],
            ['module' => 'consumables', 'field_name' => 'unit', 'label' => 'Unit', 'is_required' => true],
            ['module' => 'consumables', 'field_name' => 'min_stock', 'label' => 'Min Stock', 'is_required' => false],
            ['module' => 'consumables', 'field_name' => 'unit_price', 'label' => 'Unit Price', 'is_required' => false],
            ['module' => 'consumables', 'field_name' => 'supplier', 'label' => 'Supplier', 'is_required' => false],
            ['module' => 'consumables', 'field_name' => 'location', 'label' => 'Storage Location', 'is_required' => false],
            ['module' => 'consumables', 'field_name' => 'description', 'label' => 'Description', 'is_required' => false],

            // Tools
            ['module' => 'tools', 'field_name' => 'tool_code', 'label' => 'Tool Code', 'is_required' => false],
            ['module' => 'tools', 'field_name' => 'name', 'label' => 'Name', 'is_required' => true],
            ['module' => 'tools', 'field_name' => 'category', 'label' => 'Category', 'is_required' => false],
            ['module' => 'tools', 'field_name' => 'condition', 'label' => 'Condition', 'is_required' => true],
            ['module' => 'tools', 'field_name' => 'is_available', 'label' => 'Availability', 'is_required' => true],

            // Spare Parts
            ['module' => 'spare-parts', 'field_name' => 'part_code', 'label' => 'Part Code', 'is_required' => false],
            ['module' => 'spare-parts', 'field_name' => 'name', 'label' => 'Name', 'is_required' => true],
            ['module' => 'spare-parts', 'field_name' => 'category', 'label' => 'Category', 'is_required' => false],
            ['module' => 'spare-parts', 'field_name' => 'stock', 'label' => 'Current Stock', 'is_required' => true],
            ['module' => 'spare-parts', 'field_name' => 'unit', 'label' => 'Unit', 'is_required' => true],
            ['module' => 'spare-parts', 'field_name' => 'min_stock', 'label' => 'Min Stock', 'is_required' => false],

            // Work Orders
            ['module' => 'work-orders', 'field_name' => 'title', 'label' => 'Title', 'is_required' => true],
            ['module' => 'work-orders', 'field_name' => 'description', 'label' => 'Description', 'is_required' => true],
            ['module' => 'work-orders', 'field_name' => 'type', 'label' => 'Type', 'is_required' => true],
            ['module' => 'work-orders', 'field_name' => 'priority', 'label' => 'Priority', 'is_required' => true],
            ['module' => 'work-orders', 'field_name' => 'asset_id', 'label' => 'Asset', 'is_required' => false],
            ['module' => 'work-orders', 'field_name' => 'assigned_to', 'label' => 'Assignees', 'is_required' => false],
            ['module' => 'work-orders', 'field_name' => 'due_date', 'label' => 'Due Date', 'is_required' => false],
        ];

        foreach ($configs as $config) {
            \App\Models\FieldConfiguration::updateOrCreate(
                ['module' => $config['module'], 'field_name' => $config['field_name']],
                ['label' => $config['label'], 'is_required' => $config['is_required']]
            );
        }
    }
}
