<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;

class HomeCommand extends Command
{
    protected string $name = 'home';
    protected string $description = 'Show main menu';

    public function handle()
    {
        $message = $this->update->getMessage();
        $chatId = $message->getChat()->getId();
        $firstName = $message->getFrom()->getFirstName() ?? 'User';

        $keyboard = [
            'keyboard' => [
                [
                    ['text' => '📋 Help2', 'callback_data' => 'help'],
                    ['text' => '👷 Worker', 'callback_data' => 'worker'],
                ],
                [
                    ['text' => '⚙️ Settings', 'callback_data' => 'settings'],
                    ['text' => '🧪 Test', 'callback_data' => 'test']
                ]
            ]
        ];

        $this->replyWithMessage([
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode($keyboard),
        ]);
    }

}
