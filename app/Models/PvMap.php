<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PvMap extends Model
{
    protected $fillable = [
        'location_id',
        'transformer_block',
        'map_status',
        'map_data',
    ];

    protected $casts = [
        'map_data' => 'array',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
