<?php

namespace App\Modules\Worker\Services;

use App\Modules\Worker\Models\Shift;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use App\Modules\Worker\Services\ShiftService;
use Illuminate\Support\Str;

class WorkerButtonHandler
{
    private static $timezone = null;

    public static function handle($text, $chatId, $telegramId, $user)
    {
        self::$timezone = $user->timezone;

        // ✅ Починаємо смену
        if ($text === '✅ Start Shift') {
            ShiftService::startShift($telegramId, $chatId, self::$timezone);
            return true;
        }

        // 🛑 Завершуємо смену
        if ($text === '🛑 End Shift') {
            ShiftService::endShift($telegramId, $chatId, self::$timezone);
            return true;
        }

        // 📊 Показуємо статистику
        if ($text === '📊 Stats') {
            error_log('Stats: ' . $chatId);
            ShiftService::showStats($telegramId, $chatId, self::$timezone);
            return true;
        }

        // ⏱️ Показуємо активну смену
        if ($text === '⏱️ Active') {
            ShiftService::showActiveShift($telegramId, $chatId, self::$timezone);
            return true;
        }

        return false;
    }

    public static function callbackHandler($data, $chatId, $telegramId, $messageId, $user)
    {
        self::$timezone = $user->timezone;

        if (Str::startsWith($data, 'worker_stats_')) {

            if ($data === 'worker_stats_week') {
                ShiftService::showWeeklyStats($telegramId, $chatId, self::$timezone, $messageId);
                return true;
            } elseif ($data === 'worker_stats_month') {
                ShiftService::showMonthlyStats($telegramId, $chatId, self::$timezone, $messageId);
                return true;
            }

            ShiftService::showStats($telegramId, $chatId, self::$timezone, $messageId);

            return true;
        }
        return true;
    }
}
