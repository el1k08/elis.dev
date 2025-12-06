<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;

class HelpCommand extends Command
{
    protected string $name = 'help';
    protected string $description = 'Show available commands';

    public function handle()
    {
        $text = "📚 *Доступні Команди:*\n\n";
        $text .= "🔹 /start - Почати\n";
        $text .= "🔹 /help - Ця довідка\n";
        $text .= "🔹 /settings - Мої налаштування\n";
        $text .= "🔹 /chatid - Мій Chat ID\n";
        $text .= "🔹 /about - Про бота\n\n";
        $text .= "_Напишіть потрібну команду щоб почати_";

        $this->replyWithMessage([
            'text' => $text,
            'parse_mode' => 'Markdown'
        ]);
    }
}
