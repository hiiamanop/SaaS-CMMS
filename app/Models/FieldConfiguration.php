<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldConfiguration extends Model
{
    protected $fillable = [
        'module',
        'field_name',
        'label',
        'is_disabled',
        'is_hidden',
        'is_required',
    ];

    protected $casts = [
        'is_disabled' => 'boolean',
        'is_hidden'   => 'boolean',
        'is_required' => 'boolean',
    ];
}
