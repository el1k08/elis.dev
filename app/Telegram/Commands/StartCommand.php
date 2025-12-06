<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Laravel\Facades\Telegram;

class StartCommand extends Command
{
    protected string $name = 'start';
    protected string $description = 'Start Command';

    public function handle()
    {
        try {

            $message = $this->update->getMessage();
            $firstName = $message->getFrom()->getFirstName();
            $chatId = $message->getChat()->getId();

            $text = "👋 *Hey, $firstName!*\n\n";
            $text .= "Select a module:";

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode([
                    'keyboard' => [
                        [
                            ['text' => '👷 Worker'],
                            ['text' => '💰 Financing'],
                        ],
                    ],
                    'resize_keyboard' => true,
                ]),
            ]);

        } catch (\Exception $e) {
            \Log::error('StartCommand Error: ' . $e->getMessage());
        }
    }
}
