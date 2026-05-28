<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_code', 'location_id', 'name', 'category', 'location', 'status',
        'brand', 'model', 'serial_number', 'purchase_date',
        'purchase_price', 'warranty_expiry', 'description', 'photo',
        'transformer_block', 'string_number', 'module_slot',
        'visual_row', 'visual_col',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_expiry' => 'date',
            'purchase_price' => 'decimal:2',
        ];
    }

    public function locationId() { return $this->belongsTo(Location::class, 'location_id'); }
    public function plts() { return $this->belongsTo(Location::class, 'location_id'); }
    public function workOrders() { return $this->hasMany(WorkOrder::class); }
    public function maintenanceSchedules() { return $this->hasMany(MaintenanceSchedule::class); }
    public function maintenanceRecords() { return $this->hasMany(MaintenanceRecord::class); }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'green',
            'inactive' => 'gray',
            'replaced' => 'yellow',
            'retired' => 'red',
            default => 'gray',
        };
    }

    public function getHierarchyCodeAttribute(): ?string
    {
        if (!$this->transformer_block || !$this->string_number || !$this->module_slot) {
            return null;
        }

        $stringPad = str_pad($this->string_number, 2, '0', STR_PAD_LEFT);
        $modulePad = str_pad($this->module_slot, 2, '0', STR_PAD_LEFT);

        return "{$this->transformer_block}-INV{$stringPad}-S{$modulePad}";
    }

    public function scopeByTransformerBlock($query, $block)
    {
        return $query->where('transformer_block', $block);
    }

    public function scopeByString($query, $block, $stringNumber)
    {
        return $query->where('transformer_block', $block)
            ->where('string_number', $stringNumber);
    }

    public function scopeIsPVModule($query)
    {
        return $query->where('category', 'PV Module')->whereNotNull('transformer_block');
    }
}
