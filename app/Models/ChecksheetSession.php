<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecksheetSession extends Model
{
    protected $fillable = [
        'checksheet_type_id', 'plts_location', 'equipment_location',
        'period_label', 'year', 'week_number', 'month', 'semester',
        'status', 'submitted_at', 'submitted_by',
        'signed_by_teknisi', 'signed_date_teknisi',
        'signed_by_spv', 'signed_date_spv',
        'signed_by_pm', 'signed_date_pm',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'signed_date_teknisi' => 'date',
        'signed_date_spv' => 'date',
        'signed_date_pm' => 'date',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ChecksheetType::class, 'checksheet_type_id');
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
        $total = $this->type->templates()->count();
        $filled = $this->results()->whereNotNull('result')->count();
        return ['total' => $total, 'filled' => $filled];
    }
}
