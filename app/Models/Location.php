<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = ['name', 'capacity_mwp', 'address', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'capacity_mwp' => 'decimal:2'];
    }

    public function users()                { return $this->hasMany(User::class); }
    public function maintenanceSchedules() { return $this->hasMany(MaintenanceSchedule::class); }
}
