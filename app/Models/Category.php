<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'user_id', 'name', 'slug', 'description',
        'icon', 'color', 'type', 'parent_id',
        'sort_order', 'monthly_budget', 'budget_alert',
        'is_system', 'is_active',
    ];

    protected $casts = [
        'monthly_budget' => 'decimal:2',
        'budget_alert' => 'boolean',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }
}

