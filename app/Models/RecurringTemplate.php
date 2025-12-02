<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringTemplate extends Model
{
    protected $fillable = [
        'user_id', 'name', 'type', 'amount', 'currency',
        'category_id', 'account_id', 'to_account_id',
        'description', 'merchant',
        'frequency', 'frequency_value', 'day_of_month', 'day_of_week',
        'start_date', 'end_date', 'next_occurrence', 'last_occurrence',
        'auto_create', 'create_days_before',
        'is_active', 'total_occurrences',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_occurrence' => 'date',
        'last_occurrence' => 'date',
        'auto_create' => 'boolean',
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

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}

