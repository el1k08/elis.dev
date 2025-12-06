<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use App\Models\Shift;

class WorkCommand extends Command
{
    protected string $name = 'work';
    protected string $description = 'Track your work shift';

    public function handle()
    {
        try {
            $message = $this->update->getMessage();
            $userId = $message->getFrom()->getId();
            $username = $message->getFrom()->getUsername() ?? $message->getFrom()->getFirstName();

            // Перевірте чи є активна смена
            $activeShift = Shift::getActiveShift($userId);

            if ($activeShift) {
                // Смена вже активна
                $text = "⏱️ *На роботі!*\n";
                $text .= "Почав: `" . $activeShift->start_time->format('H:i') . "`";
            } else {
                // Немає активної смени
                $text = "👋 *Почніть смену*";
            }

            $this->replyWithMessage([
                'text' => $text,
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode([
                    'inline_keyboard' => $this->getKeyboard($activeShift)
                ]),
            ]);

        } catch (\Exception $e) {
            \Log::error('WorkCommand Exception: ' . $e->getMessage());
        }
    }

    private function getKeyboard($activeShift)
    {
        if ($activeShift) {
            return [[
                ['text' => '🛑 Стоп', 'callback_data' => 'shift_stop'],
                ['text' => '📊 Статистика', 'callback_data' => 'shift_stats']
            ]];
        } else {
            return [[
                ['text' => '✅ Старт', 'callback_data' => 'shift_start']
            ]];
        }
    }
}
