<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecksheetResult extends Model
{
    protected $fillable = ['session_id', 'template_id', 'result', 'notes', 'photos'];

    protected $casts = [
        'photos' => 'array',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChecksheetSession::class, 'session_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ChecksheetTemplate::class, 'template_id');
    }
}
