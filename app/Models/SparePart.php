<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SparePart extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'part_code', 'name', 'category', 'unit', 'qty_actual',
        'qty_minimum', 'unit_price', 'supplier', 'location', 'description',
    ];

    protected function casts(): array
    {
        return ['unit_price' => 'decimal:2'];
    }

    public function maintenanceRecordParts() { return $this->hasMany(MaintenanceRecordPart::class); }

    public function isLowStock(): bool { return $this->qty_actual <= $this->qty_minimum; }

    public function getStockPercentageAttribute(): int
    {
        if ($this->qty_minimum == 0) return 100;
        $pct = ($this->qty_actual / ($this->qty_minimum * 2)) * 100;
        return min(100, max(0, (int) $pct));
    }
}
