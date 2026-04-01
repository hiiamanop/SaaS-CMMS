<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderActivityLog extends Model
{
    protected $fillable = ['work_order_id', 'user_id', 'from_status', 'to_status', 'notes'];

    public function workOrder() { return $this->belongsTo(WorkOrder::class); }
    public function user() { return $this->belongsTo(User::class); }
}
