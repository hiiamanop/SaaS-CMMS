<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderChecklistItem extends Model
{
    protected $fillable = [
        'work_order_id', 'description', 'is_checked', 'checked_by', 'checked_at', 'order',
    ];

    protected function casts(): array
    {
        return ['is_checked' => 'boolean', 'checked_at' => 'datetime'];
    }

    public function workOrder() { return $this->belongsTo(WorkOrder::class); }
    public function checkedBy() { return $this->belongsTo(User::class, 'checked_by'); }
}
