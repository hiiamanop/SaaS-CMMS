<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionLoss extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id', 'sector_id', 'work_order_id', 'created_by',
        'started_at', 'ended_at', 'duration_minutes',
        'trafo', 'inverter', 'string_info', 'affected_strings',
        'category', 'description',
        'affected_capacity_kw', 'lop_kwh',
    ];

    protected $casts = [
        'started_at'           => 'datetime',
        'ended_at'             => 'datetime',
        'affected_capacity_kw' => 'decimal:2',
        'lop_kwh'              => 'decimal:2',
    ];

    public static array $categories = [
        'planned_maintenance'    => 'Planned Maintenance',
        'corrective_maintenance' => 'Corrective Maintenance',
        'equipment_fault'        => 'Equipment Fault',
        'grid_fault'             => 'Grid Fault',
        'natural'                => 'Natural (Cuaca Ekstrem)',
        'other'                  => 'Lainnya',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function sector()
    {
        return $this->belongsTo(ProductionSector::class, 'sector_id');
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDurationHoursAttribute(): float
    {
        return round($this->duration_minutes / 60, 2);
    }

    public function getDurationLabelAttribute(): string
    {
        $h = intdiv($this->duration_minutes, 60);
        $m = $this->duration_minutes % 60;
        return $m > 0 ? "{$h} jam {$m} menit" : "{$h} jam";
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::$categories[$this->category] ?? $this->category;
    }

    public function getCategoryColorAttribute(): string
    {
        return match($this->category) {
            'planned_maintenance'    => 'blue',
            'corrective_maintenance' => 'yellow',
            'equipment_fault'        => 'red',
            'grid_fault'             => 'purple',
            'natural'                => 'gray',
            default                  => 'gray',
        };
    }

    public function computeLopKwh(): ?float
    {
        if ($this->affected_capacity_kw && $this->duration_minutes > 0) {
            return round($this->affected_capacity_kw * ($this->duration_minutes / 60), 2);
        }
        return null;
    }
}
