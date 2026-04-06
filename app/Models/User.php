<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'avatar', 'is_active'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isSupervisor(): bool { return $this->role === 'supervisor'; }
    public function isTechnician(): bool { return $this->role === 'technician'; }
    public function isAdminOrSupervisor(): bool { return in_array($this->role, ['admin', 'supervisor']); }

    public function assignedWorkOrders() { return $this->hasMany(WorkOrder::class, 'assigned_to'); }
    public function createdWorkOrders() { return $this->hasMany(WorkOrder::class, 'created_by'); }
    public function maintenanceRecords() { return $this->hasMany(MaintenanceRecord::class, 'technician_id'); }
    public function notifications() { return $this->hasMany(Notification::class); }
    public function unreadNotifications() { return $this->hasMany(Notification::class)->where('is_read', false); }
}
