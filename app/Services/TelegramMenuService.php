<?php

namespace App\Services;

use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramMenuService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

     /**
     * Получить клавиатуру главного меню
     */
    public static function getMainMenuKeyboard()
    {
        return [
            'keyboard' => [
                [
                    ['text' => '👷 Worker'],
                    ['text' => '💰 Financing'],
                ],
                [
                    ['text' => '⚙️ Settings'],
                ],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ];
    }

    /**
     * Показать главное меню
     */
    public static function showMainMenu($chatId, $text = null, $messageId = null, $name = "User")
    {
        if (!$text) {
            $text = "👋 *Hey, {$name}!*\n\nSelect a module:";
        }

        // если есть старое сообщение меню — удаляем его
        if ($messageId) {
            Telegram::deleteMessage([
                'chat_id'    => $chatId,
                'message_id' => $messageId,
            ]);
        }

        $base = [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode(self::getMainMenuKeyboard()),
        ];

        Telegram::sendMessage($base);
    }
}
