<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReportPhoto extends Model
{
    use HasFactory;

    protected $fillable = ['daily_report_id', 'file_path'];

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class);
    }
}
