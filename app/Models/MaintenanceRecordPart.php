<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRecordPart extends Model
{
    protected $fillable = ['maintenance_record_id', 'spare_part_id', 'qty_used', 'unit_price'];

    protected function casts(): array
    {
        return ['unit_price' => 'decimal:2'];
    }

    public function maintenanceRecord() { return $this->belongsTo(MaintenanceRecord::class); }
    public function sparePart() { return $this->belongsTo(SparePart::class); }
}
