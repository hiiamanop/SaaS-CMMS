<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderItem extends Model
{
    protected $fillable = [
        'work_order_id', 'item_type', 'item_id', 'qty_used', 'unit_price',
        'created_by_user_id', 'used_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'used_at' => 'datetime',
        ];
    }

    public function workOrder() { return $this->belongsTo(WorkOrder::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function sparePart() { return $this->belongsTo(SparePart::class, 'item_id'); }
    public function consumable() { return $this->belongsTo(Consumable::class, 'item_id'); }
    public function tool() { return $this->belongsTo(Tool::class, 'item_id'); }

    public function getItemAttribute()
    {
        return match ($this->item_type) {
            'spare_part' => $this->sparePart,
            'consumable' => $this->consumable,
            'tool' => $this->tool,
            default => null,
        };
    }

    public function getItemTypeLabelAttribute(): string
    {
        return match ($this->item_type) {
            'spare_part' => 'Spare Part',
            'consumable' => 'Consumable',
            'tool' => 'Tool',
            default => ucfirst(str_replace('_', ' ', $this->item_type)),
        };
    }
}
