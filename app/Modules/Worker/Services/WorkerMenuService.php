<?php

namespace App\Modules\Worker\Services;

use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use App\Modules\Worker\Services\ShiftService;


class WorkerMenuService
{
    public static function showMainMenu($chatId, $text = null)
    {
        try {
            if (!$text) {
                $text = "👷 *Worker Module*\n\n";
                $text .= "Виберіть дію:";
            }

            $startshiftButton = ['text' => '✅ Start Shift'];
            $endshiftButton   = ['text' => '🛑 End Shift'];

            if (ShiftService::hasActiveShift($chatId)) {
                // Є активна зміна → показуємо End Shift
                $keyboard = [
                    [$endshiftButton],
                ];
            } else {
                // Немає активної зміни → показуємо Start Shift
                $keyboard = [
                    [$startshiftButton],
                ];
            }

            // нижні ряди
            $keyboard[] = [
                ['text' => '📊 Stats'],
                ['text' => '⏱️ Active'],
            ];

            $keyboard[] = [
                ['text' => '⬅️ Back'],
            ];

            $menu = json_encode([
                'keyboard'        => $keyboard,
                'resize_keyboard' => true,
            ]);

            Telegram::sendMessage([
                'chat_id'      => $chatId,
                'text'         => $text,
                'parse_mode'   => 'Markdown',
                'reply_markup' => $menu,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error starting shift', [
                'telegramId' => $telegramId,
                'error'      => $e->getMessage(),
            ]);
            return response()->json(['status' => 'ok']);
        }

    }

    public static function showShiftStarted($chatId, $shift)
    {
        $text = "✅ *Shift Started*\n\n";
        $text .= "Time: `" . $shift->start_time->format('H:i') . "`\n";
        $text .= "Date: `" . $shift->start_time->format('d.m.Y') . "`\n";
        $text .= "Status: " . $shift->getStatusBadge();

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);
    }

    public static function showShiftEnded($chatId, $shift)
    {
        $duration = $shift->getDurationInHours();
        $workingHours = $shift->getWorkingHours();

        $text = "✅ *Смена Завершена*\n\n";
        $text .= "Почало: `" . $shift->getFormattedStartTime() . "`\n";
        $text .= "Завершено: `" . $shift->getFormattedEndTime() . "`\n";
        $text .= "Загальна тривалість: `" . $duration . " год`\n";
        $text .= "Часу праці: `" . $workingHours . " год`";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);
    }

    public static function showStatsMenu($chatId, $messageId = null) {

        error_log('showStatsMenu: ' . $chatId);

        $keyboard = json_encode([
                'inline_keyboard' => [
                    [['text' => '📅 This Week', 'callback_data' => 'worker_stats_week']],
                    [['text' => '📅 This Month', 'callback_data' => 'worker_stats_month']],
                ],
            ]);

        if ($messageId) {
            // Оновлюємо існуюче повідомлення
            Telegram::editMessageText([
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => '📊 *Select a period*' . "\n\n",
                'parse_mode' => 'Markdown',
                'reply_markup' => $keyboard
            ]);
            return true;
        }

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => '📊 *Select a period*' . "\n\n",
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);

        return true;
    }

    public static function showActiveShift($chatId, $shift)
    {
        $text = "⏱️ *Active Shift*\n\n";
        $text .= "Started time: `" . $shift->start_time->format('H:i') . "`\n";
        $text .= "Date: `" . $shift->start_time->format('d.m.Y') . "`\n";

        $duration = now()->diffInMinutes($shift->start_time);
        $hours = floor($duration / 60);
        $minutes = $duration % 60;
        $text .= "Duration: `" . $hours . " hours " . $minutes . " minutes (" . number_format($hours + ($minutes / 60), 1) . ")`\n";

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);

        return true;
    }
}
