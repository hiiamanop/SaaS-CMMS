<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Finding extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'status',
        'source_type', 'checksheet_session_id', 'item_name', 'location_id', 'reported_by',
        'found_date', 'finding_time', 'resolved_date', 'close_time', 'action_taken',
    ];

    protected $casts = [
        'found_date'   => 'date',
        'finding_time' => 'datetime',
        'resolved_date'=> 'date',
        'close_time'   => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(ChecksheetSession::class, 'checksheet_session_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function checksheetResult()
    {
        return $this->hasOne(ChecksheetResult::class, 'session_id', 'checksheet_session_id')
            ->where('item_name', $this->item_name);
    }

    public function getEvidencePhotosAttribute(): array
    {
        if (!$this->checksheet_session_id || !$this->item_name) return [];

        $result = ChecksheetResult::where('session_id', $this->checksheet_session_id)
            ->where('item_name', $this->item_name)
            ->first();

        return $result?->photos ?? [];
    }

    public function getLocationLabelAttribute(): string
    {
        if ($this->source_type === 'checksheet' && $this->session) {
            $plts  = $this->session->plts_location ?? $this->session->schedule?->location?->name ?? '';
            $trafo = $this->session->schedule?->trafo_name ?? '';
            return $trafo ? "{$plts} — {$trafo}" : $plts;
        }
        return $this->location?->name ?? '';
    }

    public function getSourceLabelAttribute(): string
    {
        if ($this->source_type === 'checksheet' && $this->session) {
            $schedule = $this->session->schedule;
            return $schedule ? $schedule->title . ' — ' . $this->session->period_label : 'Checksheet';
        }
        return 'Manual';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open'        => 'red',
            'in_progress' => 'amber',
            'resolved'    => 'emerald',
            'closed'      => 'gray',
            default       => 'gray',
        };
    }
}
