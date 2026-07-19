<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyReport extends Model
{
    protected $fillable = ['year', 'month', 'location_id', 'pdf_path', 'generated_at', 'generated_by_user_id'];

    protected function casts(): array
    {
        return ['generated_at' => 'datetime'];
    }
}
