<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class SavedReport extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'filters',
        'settings',
        'is_favorite',
    ];

    protected $casts = [
        'filters' => 'array',
        'settings' => 'array',
        'is_favorite' => 'boolean',
    ];
}

