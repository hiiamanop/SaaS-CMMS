<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecksheetTemplate extends Model
{
    protected $fillable = [
        'maintenance_schedule_id', 'lokasi_inspeksi', 'item_inspeksi',
        'metode_inspeksi', 'standar_ketentuan', 'order',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MaintenanceSchedule::class, 'maintenance_schedule_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ChecksheetResult::class, 'template_id');
    }
}
