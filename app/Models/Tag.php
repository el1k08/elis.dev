<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'color',
        'description',
        'usage_count'
    ];

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

