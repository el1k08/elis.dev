<?php

namespace App\Modules\Worker\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerLog extends Model
{
    protected $table = 'worker_logs';

    protected $fillable = [
        'user_id',
        'shift_id',
        'action',
        'data',
        'ip_address',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public static function log($userId, $action, $data = [], $shiftId = null)
    {
        return self::create([
            'user_id' => $userId,
            'shift_id' => $shiftId,
            'action' => $action,
            'data' => $data,
            'ip_address' => request()->ip(),
        ]);
    }
}
