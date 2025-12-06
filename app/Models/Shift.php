<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'user_id',
        'username',
        'start_time',
        'end_time',
        'duration_minutes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Отримати активну смену користувача
     */
    public static function getActiveShift($userId)
    {
        return self::where('user_id', $userId)
            ->whereNull('end_time')
            ->first();
    }

    /**
     * Завершити смену та розрахувати час
     */
    public function endShift()
    {
        $this->end_time = now();
        $this->duration_minutes = $this->start_time->diffInMinutes($this->end_time);
        $this->save();

        return $this;
    }

    /**
     * Отримати тривалість у форматі "2h 30m"
     */
    public function getFormattedDuration()
    {
        if (!$this->duration_minutes) {
            return null;
        }

        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        return "{$hours}h {$minutes}m";
    }
}
