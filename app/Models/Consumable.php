<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consumable extends Model
{
    protected $guarded = [];

    public function workOrderItems() { return $this->hasMany(WorkOrderItem::class, 'item_id')->where('item_type', 'consumable'); }
    public function maintenanceRecordConsumables() { return $this->hasMany(MaintenanceRecordConsumable::class); }
}
