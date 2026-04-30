<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_report_id', 'sector_id',
        'kwh_trafo', 'kwh_meter', 'sun_hour', 'capacity_factor', 'performance_ratio',
    ];

    protected $casts = [
        'kwh_trafo'         => 'decimal:2',
        'kwh_meter'         => 'decimal:2',
        'sun_hour'          => 'decimal:2',
        'capacity_factor'   => 'decimal:4',
        'performance_ratio' => 'decimal:4',
    ];

    public function report()
    {
        return $this->belongsTo(ProductionReport::class, 'production_report_id');
    }

    public function sector()
    {
        return $this->belongsTo(ProductionSector::class, 'sector_id');
    }

    public function computeCfAndPr(): array
    {
        $kwp = (float) ($this->sector?->capacity_kwp ?? 0);
        $sh  = (float) ($this->sun_hour ?? 0);
        $kwh = (float) ($this->kwh_meter ?? 0);

        if ($kwp <= 0 || $sh <= 0 || $kwh <= 0) {
            return ['cf' => null, 'pr' => null];
        }

        // CF  = kWh_meter / (kWp × sun_hour)
        // PR  = same formula (sun_hour used as peak irradiance proxy in kWh/m²)
        $cf = round($kwh / ($kwp * $sh), 4);

        return ['cf' => $cf, 'pr' => $cf];
    }
}
