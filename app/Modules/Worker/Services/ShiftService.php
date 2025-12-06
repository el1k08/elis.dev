<?php

namespace App\Modules\Worker\Services;

use App\Modules\Worker\Models\Shift;
use App\Modules\Worker\Models\WorkerLog;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use App\Modules\Worker\Services\WorkerMenuService;

class ShiftService
{
    // отримуємо активну смену
    public static function getActiveShift($telegramId)
    {
        $activeShift = Shift::where('telegram_id', $telegramId)
            ->whereNull('end_time')
            ->first();

        return $activeShift;
    }

    // проверяем есть ли активная смена
    public static function hasActiveShift($telegramId)
    {
        return self::getActiveShift($telegramId) !== null;
    }

    // починаємо смену
    public static function startShift($telegramId, $chatId, $timezone){
        try {

            $timezone = $timezone ?? 'UTC';

            Log::info('Checking for active shift', ['telegramId' => $telegramId]);
            $shift = self::getActiveShift($telegramId);

            if ($shift) {
                Log::info('Active shift already exists', ['telegramId' => $telegramId]);

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "⚠️ *Active shift already started!*\n\n" .
                            '*Timezone:* ' . str_replace('_', ' ', $timezone) . "\n" .
                            '*Started:* ' . self::getLocalTime($shift->start_time, $timezone) . "\n\n" .
                            'Please end it before starting a new one.',
                    'parse_mode' => 'Markdown'
                ]);

                return true;
            }

            // Створюємо нову смену
            Log::info('Creating new shift', ['telegramId' => $telegramId]);

            $shift = Shift::create([
                'telegram_id' => $telegramId,
                'start_time' => now('UTC'),
                'status' => 'active',
            ]);

            Log::info('New shift started', [
                'telegramId' => $telegramId,
                'shiftId' => $shift->id,
                'time' => $shift->start_time
            ]);

            $text = "✅ *Shift started!*\n\n"
                . '⏰ Time: ' . self::getLocalTime($shift->start_time, $timezone);

            WorkerMenuService::showMainMenu($chatId, $text);

            return true;

        } catch (\Exception $e) {
            Log::error('Error starting shift', [
                'telegramId' => $telegramId,
                'error' => $e->getMessage()
            ]);

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Помилка при розпочатті смени',
            ]);

            return true;
        }
    }

    // завершуємо смену
    public static function endShift($telegramId, $chatId, $timezone){
        try {

            $shift = Shift::where('telegram_id', $telegramId)
                ->whereNull('end_time')
                ->first();

            if (!$shift) {
                Log::info('No active shift found', ['telegramId' => $telegramId]);
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => '⚠️ *No active shift found!*',
                    'parse_mode' => 'Markdown'
                ]);
                return true;
            }

            $shift->update([
                'end_time' => now('UTC'),
                'status' => 'completed',
            ]);

            $startUtc = $shift->start_time->clone()->setTimezone('UTC');
            $endUtc   = $shift->end_time->clone()->setTimezone('UTC');

            $totalMinutes = $startUtc->diffInMinutes($endUtc);      // целое
            $hours        = intdiv($totalMinutes, 60);              // целые часы
            $minutes      = $totalMinutes % 60;                     // остаток минут
            $hoursDecimal = round($totalMinutes / 60, 2);           // например 7.5

            $durationText =
                $hours . ' hour(s) ' .
                $minutes . ' min(s) ' .
                '(' . $hoursDecimal . ')';

            $duration = $durationText;

            Log::info('Shift ended', [
                'telegramId' => $telegramId,
                'shiftId' => $shift->id,
                'duration' => $duration
            ]);

            $text = "🛑 *Shift Ended*\n\n"
                . "Start: " . self::getLocalTime($shift->start_time, $timezone) . "\n"
                . "End: " . self::getLocalTime($shift->end_time, $timezone) . "\n"
                . "Duration: " . $duration;

            WorkerMenuService::showMainMenu($chatId, $text);

            return true;

        } catch (\Exception $e) {
            Log::error('Error ending shift', [
                'telegramId' => $telegramId,
                'error' => $e->getMessage()
            ]);

            return true;
        }
    }

    // показуємо меню статистики
    public static function showStats($telegramId, $chatId, $timezone, $messageId = null) {
        WorkerMenuService::showStatsMenu($chatId, $messageId);
        return true;
    }

    // показуємо cтатистику за тиждень
    public static function showWeeklyStats($telegramId, $chatId, $timezone, $messageId) {

        $stats = Shift::getUserStats($telegramId, now()->startOfWeek(), now()->endOfWeek());

        $message = "📊 *Weekly Statistics*\n\n" .
                "Total shifts: " . $stats['total_shifts'] . "\n" .
                "Total hours: " . $stats['total_hours'] . "\n" .
                "Average duration: " . $stats['average_shift_duration'];

        Telegram::editMessageText([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => '🔙 Back', 'callback_data' => 'worker_stats_back']],
                ],
            ])
        ]);

        return true;
    }

    // показуємо cтатистику за місяць
    public static function showMonthlyStats($telegramId, $chatId, $timezone, $messageId) {

        $stats = Shift::getUserStats($telegramId, now()->startOfMonth(), now()->endOfMonth());

        $message = "📊 *Monthly Statistics*\n\n" .
                "Total shifts: " . $stats['total_shifts'] . "\n" .
                "Total hours: " . $stats['total_hours'] . "\n" .
                "Average duration: " . $stats['average_shift_duration'];

        Telegram::editMessageText([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => '🔙 Back', 'callback_data' => 'worker_stats_back']],
                ],
            ])
        ]);

        return true;
    }

    public static function showActiveShift($telegramId, $chatId, $timezone) {
        $shift = self::getActiveShift($telegramId);

        if (!$shift) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => '⚠️ *No active shift found!*',
                'parse_mode' => 'Markdown'
            ]);
            return true;
        }

        $text = "⏱️ *Active Shift*\n\n";
        $text .= "Started time: `" . self::getLocalTime($shift->start_time, $timezone) . "`\n";
        $text .= "Date: `" . $shift->start_time->format('d.m.Y') . "`\n";

        // продолжительность смены в минутах
        $duration = $shift->start_time->diffInMinutes($shift->end_time);  // или now() если ещё идёт

        $hours   = intdiv($duration, 60);  // целые часы
        $minutes = $duration % 60;         // остаток минут

        $hoursDecimal = round($duration / 60, 2);

        $text .= "Duration: `{$hours} hours {$minutes} minutes ({$hoursDecimal})`";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);

        return true;
    }

    // отримуємо локальний час
    private static function getLocalTime($time, $timezone) {
        return $time->setTimezone($timezone)->format('h:i A');
    }
}
