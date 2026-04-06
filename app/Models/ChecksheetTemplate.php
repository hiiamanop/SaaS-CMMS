<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecksheetTemplate extends Model
{
    protected $fillable = [
        'checksheet_type_id', 'lokasi_inspeksi', 'item_inspeksi',
        'metode_inspeksi', 'standar_ketentuan', 'order',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ChecksheetType::class, 'checksheet_type_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ChecksheetResult::class, 'template_id');
    }
}
