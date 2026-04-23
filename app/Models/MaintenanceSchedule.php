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
        'title', 'location_id', 'technician_id', 'type', 'frequency', 'frequency_days',
        'start_date', 'next_due_date', 'last_done_date', 'status', 'notes',
        'category', 'equipment_name', 'trafo_name', 'item_pekerjaan',
        'planned_weeks', 'shutdown_required', 'shutdown_duration_hours', 'checklist_template',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date'          => 'date',
            'next_due_date'       => 'date',
            'last_done_date'      => 'date',
            'planned_weeks'       => 'array',
            'item_pekerjaan'      => 'array',
            'checklist_template'  => 'array',
            'shutdown_required'   => 'boolean',
        ];
    }

    public function location()           { return $this->belongsTo(Location::class); }
    public function technician()         { return $this->belongsTo(User::class, 'technician_id'); }
    public function deletedBy()          { return $this->belongsTo(User::class, 'deleted_by'); }
    public function checksheetSessions() { return $this->hasMany(ChecksheetSession::class); }
    public function workOrders()         { return $this->hasMany(WorkOrder::class); }

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
            'daily'     => $base->addDay(),
            'weekly'    => $base->addWeek(),
            'monthly'   => $base->addMonth(),
            'triwulan'  => $base->addMonths(3),
            'quarterly' => $base->addMonths(6),
            'annually'  => $base->addYear(),
            'custom'    => $base->addDays($this->frequency_days ?? 30),
            default    => $base->addMonth(),
        };
    }

    /**
     * Human-readable list of item_pekerjaan as string (Inspection Items).
     */
    public function getItemPekerjaanTextAttribute(): string
    {
        $items = $this->item_pekerjaan;
        if (!is_array($items)) return $items ?? '';
        return implode(', ', array_unique(array_filter(array_map(
            fn($i) => is_array($i) ? ($i['name'] ?? '') : $i,
            $items
        ))));
    }

    /**
     * Human-readable list of unique lokasi_inspeksi as string.
     */
    public function getLokasiInspeksiTextAttribute(): string
    {
        $items = $this->item_pekerjaan;
        if (!is_array($items)) return '';
        
        $locations = array_unique(array_filter(array_map(
            fn($i) => is_array($i) ? ($i['lokasi_inspeksi'] ?? '') : '',
            $items
        )));

        return implode(', ', $locations);
    }

    /**
     * Build the period label for a session.
     */
    public function periodLabel(array $data): string
    {
        return match($this->frequency) {
            'weekly'    => 'Week ' . ($data['week_number'] ?? 1) . ' - ' . Carbon::createFromDate($data['year'], $data['month'] ?? 1, 1)->format('M Y'),
            'monthly'   => Carbon::createFromDate($data['year'], $data['month'] ?? 1, 1)->format('F Y'),
            'triwulan'  => 'Kuartal ' . ($data['quarter'] ?? 1) . ' ' . $data['year'],
            'quarterly' => 'Semester ' . ($data['semester'] ?? 1) . ' ' . $data['year'],
            'annually'  => (string) $data['year'],
            default     => (string) $data['year'],
        };
    }

    /**
     * Auto-generate checksheet sessions for the given year based on frequency and planned_weeks.
     * Returns the number of sessions created.
     */
    public function generateYearSessions(?int $year = null): int
    {
        $year ??= now()->year;
        $created = 0;

        $periodParams = [];

        switch ($this->frequency) {
            case 'weekly':
                foreach ($this->planned_weeks ?? [] as $pw) {
                    $periodParams[] = ['month' => $pw['month'], 'week_number' => $pw['week']];
                }
                break;

            case 'monthly':
                // Use planned_weeks to get which months are planned (week field is ignored for monthly)
                $plannedMonths = collect($this->planned_weeks ?? [])
                    ->pluck('month')
                    ->unique()
                    ->sort()
                    ->values();
                // Fall back to all 12 months if no planned_weeks configured
                $months = $plannedMonths->isNotEmpty() ? $plannedMonths->all() : range(1, 12);
                foreach ($months as $m) {
                    $periodParams[] = ['month' => $m];
                }
                break;

            case 'triwulan':
                $periodParams = [['quarter' => 1], ['quarter' => 2], ['quarter' => 3], ['quarter' => 4]];
                break;
            case 'quarterly':
                $periodParams[] = ['semester' => 1];
                $periodParams[] = ['semester' => 2];
                break;

            case 'annually':
                $periodParams = [[]];
                break;
        }

        foreach ($periodParams as $params) {
            $q = ChecksheetSession::where('maintenance_schedule_id', $this->id)->where('year', $year);

            // Uniqueness: match on ALL period fields (NULL = not set)
            $q->where(fn($q) => $q
                ->when(isset($params['month']),       fn($q) => $q->where('month', $params['month']),       fn($q) => $q->whereNull('month'))
                ->when(isset($params['week_number']), fn($q) => $q->where('week_number', $params['week_number']), fn($q) => $q->whereNull('week_number'))
                ->when(isset($params['quarter']),     fn($q) => $q->where('quarter', $params['quarter']),   fn($q) => $q->whereNull('quarter'))
                ->when(isset($params['semester']),    fn($q) => $q->where('semester', $params['semester']),  fn($q) => $q->whereNull('semester'))
            );

            if ($q->exists()) continue;

            $allParams = array_merge(['year' => $year], $params);

            ChecksheetSession::create([
                'maintenance_schedule_id' => $this->id,
                'plts_location'           => $this->location?->name ?? $this->equipment_name,
                'equipment_location'      => $this->equipment_name,
                'period_label'            => $this->periodLabel($allParams),
                'year'                    => $year,
                'week_number'             => $params['week_number'] ?? null,
                'month'                   => $params['month'] ?? null,
                'semester'                => $params['semester'] ?? null,
                'status'                  => 'draft',
            ]);
            $created++;
        }

        return $created;
    }
}
