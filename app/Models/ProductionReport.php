<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id', 'created_by', 'report_date',
        'cerah_hours', 'berawan_hours', 'mendung_hours', 'hujan_hours', 'notes',
    ];

    protected $casts = [
        'report_date' => 'date',
        'cerah_hours' => 'decimal:2',
        'berawan_hours' => 'decimal:2',
        'mendung_hours' => 'decimal:2',
        'hujan_hours' => 'decimal:2',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function entries()
    {
        return $this->hasMany(ProductionEntry::class)->with('sector')->orderBy('sector_id');
    }

    public function getTotalKwhTrafoAttribute(): float
    {
        return (float) $this->entries->sum('kwh_trafo');
    }

    public function getTotalKwhMeterAttribute(): float
    {
        return (float) $this->entries->sum('kwh_meter');
    }

    public function getWeatherSummaryAttribute(): string
    {
        $parts = [];
        if ($this->cerah_hours > 0)   $parts[] = "Cerah {$this->cerah_hours}j";
        if ($this->berawan_hours > 0) $parts[] = "Berawan {$this->berawan_hours}j";
        if ($this->mendung_hours > 0) $parts[] = "Mendung {$this->mendung_hours}j";
        if ($this->hujan_hours > 0)   $parts[] = "Hujan {$this->hujan_hours}j";
        return implode(', ', $parts) ?: '-';
    }
}
