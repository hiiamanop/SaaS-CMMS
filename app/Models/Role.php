<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = ['name', 'guard_name', 'label', 'description'];

    // Roles used by the system internally — cannot be deleted
    public const PROTECTED = ['admin', 'supervisor', 'technician', 'developer'];

    public function isProtected(): bool
    {
        return in_array($this->name, self::PROTECTED);
    }
}
