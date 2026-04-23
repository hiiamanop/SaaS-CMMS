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
            'under_maintenance' => 'yellow',
            'retired' => 'red',
            default => 'gray',
        };
    }
}
