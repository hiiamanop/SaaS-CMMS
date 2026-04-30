<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id', 'sector_id', 'year', 'month',
        'target_kwh_daily', 'target_pr', 'notes',
    ];

    protected $casts = [
        'target_kwh_daily' => 'decimal:2',
        'target_pr'        => 'decimal:4',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function sector()
    {
        return $this->belongsTo(ProductionSector::class, 'sector_id');
    }

    public function getMonthNameAttribute(): string
    {
        return \Carbon\Carbon::create($this->year, $this->month, 1)->translatedFormat('F Y');
    }
}
