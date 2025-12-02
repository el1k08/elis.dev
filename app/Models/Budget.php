<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id', 'category_id', 'name',
        'amount', 'currency',
        'period_type', 'start_date', 'end_date',
        'spent_amount', 'remaining_amount',
        'alert_enabled', 'alert_threshold', 'alert_sent',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'alert_enabled' => 'boolean',
        'alert_sent' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}

