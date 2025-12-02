<?php

namespace App\Models;

use App\Models\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use BelongsToUser;

    protected $fillable = [
        'user_id', 'name', 'type',
        'initial_balance', 'current_balance',
        'currency', 'institution', 'account_number', 'routing_number',
        'credit_limit', 'billing_day', 'payment_due_day',
        'interest_rate', 'loan_start_date', 'loan_end_date',
        'icon', 'color', 'include_in_total', 'is_active', 'sort_order', 'notes',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'loan_start_date' => 'date',
        'loan_end_date' => 'date',
        'include_in_total' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(Transaction::class, 'account_id')
            ->where('type', 'transfer');
    }

    public function incomingTransfers()
    {
        return $this->hasMany(Transaction::class, 'to_account_id')
            ->where('type', 'transfer');
    }

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }
}

