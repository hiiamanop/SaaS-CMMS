<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecksheetAbnormal extends Model
{
    protected $fillable = [
        'session_id', 'tanggal', 'abnormal_description',
        'penanganan', 'tgl_selesai', 'pic',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tgl_selesai' => 'date',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChecksheetSession::class, 'session_id');
    }
}
