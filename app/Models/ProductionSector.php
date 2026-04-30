<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionSector extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id', 'name', 'capacity_kwp', 'capacity_kwac', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capacity_kwp' => 'decimal:2',
        'capacity_kwac' => 'decimal:2',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function entries()
    {
        return $this->hasMany(ProductionEntry::class, 'sector_id');
    }
}
