<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedReport extends Model
{
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

