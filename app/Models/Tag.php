<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'color',
        'description',
        'usage_count'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Polymorphic many-to-many
    public function transactions()
    {
        return $this->morphedByMany(Transaction::class, 'taggable');
    }

    public function budgets()
    {
        return $this->morphedByMany(Budget::class, 'taggable');
    }

    public function goals()
    {
        return $this->morphedByMany(Goal::class, 'taggable');
    }
}

