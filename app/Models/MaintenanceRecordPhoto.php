<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRecordPhoto extends Model
{
    protected $fillable = ['maintenance_record_id', 'file_path', 'caption'];

    public function maintenanceRecord() { return $this->belongsTo(MaintenanceRecord::class); }
}
