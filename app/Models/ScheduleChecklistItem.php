<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleChecklistItem extends Model
{
    protected $fillable = ['maintenance_schedule_id', 'description', 'order'];

    public function schedule() { return $this->belongsTo(MaintenanceSchedule::class, 'maintenance_schedule_id'); }
}
