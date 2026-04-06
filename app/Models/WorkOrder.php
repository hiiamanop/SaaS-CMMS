<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'wo_number', 'title', 'asset_id', 'assigned_to', 'assigned_to_external', 'created_by',
        'maintenance_schedule_id', 'type', 'priority', 'status',
        'due_date', 'start_date', 'started_at', 'completed_at', 'description', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'start_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function asset() { return $this->belongsTo(Asset::class); }
    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function assignees() { return $this->belongsToMany(User::class, 'work_order_assignees'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function maintenanceSchedule() { return $this->belongsTo(MaintenanceSchedule::class); }
    public function checklistItems() { return $this->hasMany(WorkOrderChecklistItem::class)->orderBy('order'); }
    public function activityLogs() { return $this->hasMany(WorkOrderActivityLog::class)->latest(); }
    public function maintenanceRecord() { return $this->hasOne(MaintenanceRecord::class); }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'preventive_mingguan' => 'Preventive — Mingguan',
            'preventive_bulanan'  => 'Preventive — Bulanan',
            'preventive_semesteran' => 'Preventive — Semesteran',
            'preventive_tahunan'  => 'Preventive — Tahunan',
            'corrective'          => 'Corrective',
            'emergency'           => 'Emergency',
            'preventive'          => 'Preventive',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'preventive_mingguan', 'preventive_bulanan',
            'preventive_semesteran', 'preventive_tahunan', 'preventive' => 'blue',
            'corrective' => 'orange',
            'emergency'  => 'red',
            default => 'gray',
        };
    }

    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && !in_array($this->status, ['closed']);
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'gray',
            'medium' => 'blue',
            'high' => 'orange',
            'critical' => 'red',
            default => 'gray',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'blue',
            'in_progress' => 'yellow',
            'pending_review' => 'purple',
            'closed' => 'green',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'pending_review' => 'Pending Review',
            'closed' => 'Closed',
            default => ucfirst($this->status),
        };
    }

    public static function generateNumber(): string
    {
        $prefix = 'WO-' . date('Ym') . '-';
        $last = static::where('wo_number', 'like', $prefix . '%')->latest()->first();
        $seq = $last ? ((int) substr($last->wo_number, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
