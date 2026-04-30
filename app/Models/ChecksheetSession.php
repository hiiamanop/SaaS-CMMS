<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecksheetSession extends Model
{
    protected $fillable = [
        'maintenance_schedule_id', 'plts_location', 'equipment_location',
        'period_label', 'year', 'week_number', 'month', 'semester',
        'status', 'submitted_at', 'submitted_by',
        'signed_by_teknisi', 'signed_date_teknisi',
        'signed_by_spv', 'signed_date_spv',
        'signed_by_pm', 'signed_date_pm',
    ];

    protected $casts = [
        'submitted_at'       => 'datetime',
        'signed_date_teknisi' => 'date',
        'signed_date_spv'    => 'date',
        'signed_date_pm'     => 'date',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MaintenanceSchedule::class, 'maintenance_schedule_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ChecksheetResult::class, 'session_id');
    }

    public function abnormals(): HasMany
    {
        return $this->hasMany(ChecksheetAbnormal::class, 'session_id');
    }

    public function getProgressAttribute(): array
    {
        $items  = $this->schedule->item_pekerjaan ?? [];
        $total  = is_array($items) ? count($items) : 0;
        $filled = $this->results()->whereNotNull('result')->count();
        return ['total' => $total, 'filled' => $filled];
    }

    public function getDueDateAttribute(): \Carbon\Carbon
    {
        $year = $this->year ?? now()->year;
        $freq = $this->schedule->frequency ?? 'monthly';
        
        return match($freq) {
            'weekly'    => \Carbon\Carbon::createFromDate($year, $this->month ?? 1, 1)->addWeeks($this->week_number ?? 1)->subDay()->endOfDay(),
            'monthly'   => \Carbon\Carbon::createFromDate($year, $this->month ?? 1, 1)->endOfMonth(),
            'triwulan'  => match((int)($this->quarter ?? 1)) {
                1 => \Carbon\Carbon::createFromDate($year, 3, 31)->endOfDay(),
                2 => \Carbon\Carbon::createFromDate($year, 6, 30)->endOfDay(),
                3 => \Carbon\Carbon::createFromDate($year, 9, 30)->endOfDay(),
                default => \Carbon\Carbon::createFromDate($year, 12, 31)->endOfDay(),
            },
            'quarterly' => ($this->semester == 1)
                ? \Carbon\Carbon::createFromDate($year, 6, 30)->endOfDay()
                : \Carbon\Carbon::createFromDate($year, 12, 31)->endOfDay(),
            'annually'  => \Carbon\Carbon::createFromDate($year, 12, 31)->endOfDay(),
            default     => \Carbon\Carbon::createFromDate($year, 12, 31)->endOfDay(),
        };
    }
}
