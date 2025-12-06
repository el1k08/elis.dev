<?php

namespace App\Modules\Worker\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';

    protected $fillable = [
        'telegram_id',  // ← Замість user_id
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'notes',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];

    // ✅ Пошук активної смени за Telegram ID
    public static function getActiveShift($telegramId)
    {
        return self::where('telegram_id', $telegramId)
            ->whereNull('end_time')
            ->first();
    }

    public static function hasActiveShift($telegramId)
    {
        return self::where('telegram_id', $telegramId)
            ->whereNull('end_time')
            ->exists();
    }

    public function getDurationInHours()
    {
        if (!$this->end_time) {
            return null;
        }
        return $this->end_time->diffInHours($this->start_time);
    }

    public function getWorkingHours()
    {
        if (!$this->end_time) {
            return null;
        }

        $totalHours = $this->getDurationInHours();
        $breakHours = 0;

        if ($this->break_start && $this->break_end) {
            $breakHours = $this->break_end->diffInHours($this->break_start);
        }

        return $totalHours - $breakHours;
    }

    // ✅ Статистика за Telegram ID
    public static function getUserStats($telegramId, $startDate = null, $endDate = null)
    {
        $query = self::where('telegram_id', $telegramId)->whereNotNull('end_time');

        if ($startDate) {
            $query->whereDate('start_time', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('end_time', '<=', $endDate);
        }


        $shifts = $query->get();

        $totalShifts = $shifts->count();

        // calculate everything in minutes
        $totalMinutes = $shifts->sum(function ($shift) {
            return $shift->getDurationInMinutes() ?? 0;
        });

        // hours/minutes + decimal hours
        $totalHoursInt   = intdiv($totalMinutes, 60);
        $totalMinutesRem = $totalMinutes % 60;
        $totalHoursFloat = round($totalMinutes / 60, 2);

        // average shift in minutes
        $avgMinutes = $totalShifts > 0 ? intdiv($totalMinutes, $totalShifts) : 0;
        $avgHours   = intdiv($avgMinutes, 60);
        $avgMinRem  = $avgMinutes % 60;

        return [
            'total_shifts' => $totalShifts,

            // example: "32 hours 30 min (32.5)"
            'total_hours' => sprintf(
            '%d hours %d min (%.2f)',
            $totalHoursInt,
            $totalMinutesRem,
            $totalHoursFloat
            ),

            // you can also return raw float if needed
            'total_hours_float' => $totalHoursFloat,

            // average shift duration: "8 hours 0 min"
            'average_shift_duration' => sprintf(
            '%d hours %d min',
            $avgHours,
            $avgMinRem
            ),
        ];
    }

    public static function getTodayShifts($telegramId)
    {
        return self::where('telegram_id', $telegramId)
            ->whereDate('start_time', today())
            ->get();
    }

    public static function getWeekShifts($telegramId)
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        return self::where('telegram_id', $telegramId)
            ->whereBetween('start_time', [$startOfWeek, $endOfWeek])
            ->get();
    }

    public static function getMonthShifts($telegramId)
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return self::where('telegram_id', $telegramId)
            ->whereBetween('start_time', [$startOfMonth, $endOfMonth])
            ->get();
    }

    public function getFormattedStartTime()
    {
        return $this->start_time->format('H:i d.m.Y');
    }

    public function getFormattedEndTime()
    {
        return $this->end_time ? $this->end_time->format('H:i d.m.Y') : 'Не завершена';
    }

    public function getStatusBadge()
    {
        if (!$this->end_time) {
            return '🟢 Active';
        }

        return '✅ Completed';
    }

    public function getDurationInMinutes(): ?int
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        return $this->start_time->diffInMinutes($this->end_time);
    }

}
