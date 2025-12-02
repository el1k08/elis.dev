<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    protected $fillable = [
        'user_id', 'account_id', 'name', 'description',
        'target_amount', 'current_amount', 'currency',
        'target_date', 'achieved_date',
        'recommended_monthly_saving',
        'icon', 'color', 'status', 'progress_percentage',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'recommended_monthly_saving' => 'decimal:2',
        'target_date' => 'date',
        'achieved_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }
}

