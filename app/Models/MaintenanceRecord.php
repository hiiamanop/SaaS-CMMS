<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'record_number', 'work_order_id', 'asset_id', 'technician_id',
        'type', 'maintenance_date', 'findings', 'actions_taken',
        'duration_minutes', 'shutdown_minutes', 'notes',
    ];

    protected function casts(): array
    {
        return ['maintenance_date' => 'date'];
    }

    public function workOrder() { return $this->belongsTo(WorkOrder::class); }
    public function asset() { return $this->belongsTo(Asset::class); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id'); }
    public function parts() { return $this->hasMany(MaintenanceRecordPart::class); }
    public function photos() { return $this->hasMany(MaintenanceRecordPhoto::class); }

    public static function generateNumber(): string
    {
        $prefix = 'MR-' . date('Ym') . '-';
        $last = static::withTrashed()->where('record_number', 'like', $prefix . '%')->orderByDesc('record_number')->first();
        $seq = $last ? ((int) substr($last->record_number, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function getDurationHoursAttribute(): float
    {
        return round($this->duration_minutes / 60, 2);
    }

    public function getShutdownHoursAttribute(): float
    {
        return round($this->shutdown_minutes / 60, 2);
    }
}
