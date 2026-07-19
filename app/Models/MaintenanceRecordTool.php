<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRecordTool extends Model
{
    protected $fillable = ['maintenance_record_id', 'tool_id'];

    public function maintenanceRecord() { return $this->belongsTo(MaintenanceRecord::class); }
    public function tool() { return $this->belongsTo(Tool::class); }
}
