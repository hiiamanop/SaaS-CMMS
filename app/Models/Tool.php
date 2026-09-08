<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    protected $guarded = [];

    public function workOrderItems() { return $this->hasMany(WorkOrderItem::class, 'item_id')->where('item_type', 'tool'); }
    public function maintenanceRecordTools() { return $this->hasMany(MaintenanceRecordTool::class); }
}
