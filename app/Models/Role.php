<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'label', 'description'];

    // Roles used by the system internally — cannot be deleted
    public const PROTECTED = ['admin', 'supervisor', 'technician'];

    public function isProtected(): bool
    {
        return in_array($this->name, self::PROTECTED);
    }
}
