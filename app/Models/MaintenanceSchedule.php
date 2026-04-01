<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class MaintenanceSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'asset_id', 'type', 'frequency', 'frequency_days',
        'start_date', 'next_due_date', 'last_done_date', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'next_due_date' => 'date',
            'last_done_date' => 'date',
        ];
    }

    public function asset() { return $this->belongsTo(Asset::class); }
    public function checklistItems() { return $this->hasMany(ScheduleChecklistItem::class)->orderBy('order'); }
    public function workOrders() { return $this->hasMany(WorkOrder::class); }

    public function isOverdue(): bool
    {
        return $this->next_due_date->isPast() && $this->status === 'active';
    }

    public function getNextDueDaysAttribute(): int
    {
        return now()->diffInDays($this->next_due_date, false);
    }

    public function calculateNextDueDate(): Carbon
    {
        $base = $this->last_done_date ?? $this->start_date;
        return match($this->frequency) {
            'daily' => $base->addDay(),
            'weekly' => $base->addWeek(),
            'monthly' => $base->addMonth(),
            'quarterly' => $base->addMonths(3),
            'annually' => $base->addYear(),
            'custom' => $base->addDays($this->frequency_days ?? 30),
            default => $base->addMonth(),
        };
    }
}
