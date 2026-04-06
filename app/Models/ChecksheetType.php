<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChecksheetType extends Model
{
    protected $fillable = ['name', 'frequency'];

    public function templates(): HasMany
    {
        return $this->hasMany(ChecksheetTemplate::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ChecksheetSession::class);
    }
}
