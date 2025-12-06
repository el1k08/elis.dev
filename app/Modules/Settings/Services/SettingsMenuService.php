<?php

namespace App\Modules\Settings\Services;

use App\Models\TelegramUser;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;

class SettingsMenuService
{
    /**
    * ✅ Show main settings menu
     */
    public static function showMainMenu($chatId, $user, $messageId = null)
    {
        $menu = "⚙️ *Settings*\n\n" .
                "👤 Name: " . ($user->first_name ?? 'Not specified') . "\n" .
                "🌍 Timezone: " . str_replace('_', ' ', $user->timezone) . "\n" .
                "*Select what to change:*";

        $message = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $menu,
            'parse_mode' => 'Markdown',
            'disable_notification' => true,
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => '🌍 Timezone', 'callback_data' => 'settings_timezone']],
                    [['text' => '✏️ Edit Name', 'callback_data' => 'settings_edit_name']],
                    [['text' => '❓ Help', 'callback_data' => 'settings_help']],
                    [['text' => '⬅️ Back', 'callback_data' => 'settings_back']],
                ],
            ]),
        ];

        if($messageId) {
            Telegram::editMessageText($message);
        } else {
            Telegram::sendMessage($message);
        }

        return true;
    }

    /**
    * ✅ Show timezone selection menu
     */
    public static function showTimezoneMenu($chatId, $user, $messageId)
    {
        $currentTz = $user?->timezone ?? 'UTC';
        $timezoneSafe = str_replace('_', ' ', $currentTz);

        $message = "🌍 *Your Timezone*\n\n";
        $message .= "Current: `{$timezoneSafe}`\n\n";
        $message .= "Select your timezone:";

        $keyboard = json_encode([
            'inline_keyboard' => [

                // Popular timezones
                [['text' => '🇨🇦 Newfoundland (UTC-3:30)', 'callback_data' => 'settings_timezone_America/St_Johns']],
                [['text' => '🇨🇦 Atlantic (UTC-4)', 'callback_data' => 'settings_timezone_America/Halifax']],
                [['text' => '🇨🇦 Eastern (UTC-5)', 'callback_data' => 'settings_timezone_America/Toronto']],
                [['text' => '🇨🇦 Central (UTC-6)', 'callback_data' => 'settings_timezone_America/Winnipeg']],
                [['text' => '🇨🇦 Mountain (UTC-7)', 'callback_data' => 'settings_timezone_America/Edmonton']],
                [['text' => '🇨🇦 Pacific (UTC-8)', 'callback_data' => 'settings_timezone_America/Vancouver']],

                // Other popular
                [['text' => '🌐 UTC', 'callback_data' => 'settings_timezone_UTC']],
                [['text' => '🇬🇧 London (UTC+0)', 'callback_data' => 'settings_timezone_Europe/London']],
                [['text' => '🇩🇪 Berlin (UTC+1)', 'callback_data' => 'settings_timezone_Europe/Berlin']],
                [['text' => '🇫🇷 Paris (UTC+1)', 'callback_data' => 'settings_timezone_Europe/Paris']],
                [['text' => '🇮🇹 Rome (UTC+1)', 'callback_data' => 'settings_timezone_Europe/Rome']],
                [['text' => '🇪🇸 Madrid (UTC+1)', 'callback_data' => 'settings_timezone_Europe/Madrid']],
                [['text' => '🇵🇱 Warsaw (UTC+1)', 'callback_data' => 'settings_timezone_Europe/Warsaw']],
                [['text' => '🇺🇦 Kyiv (UTC+2)', 'callback_data' => 'settings_timezone_Europe/Kiev']],
                [['text' => '🇬🇷 Athens (UTC+2)', 'callback_data' => 'settings_timezone_Europe/Athens']],
                [['text' => '🇮🇱 Jerusalem (UTC+2)', 'callback_data' => 'settings_timezone_Asia/Jerusalem']],
                [['text' => '🇹🇷 Istanbul (UTC+3)', 'callback_data' => 'settings_timezone_Europe/Istanbul']],
                [['text' => '🇯🇵 Tokyo (UTC+9)', 'callback_data' => 'settings_timezone_Asia/Tokyo']],

                // Back button
                [['text' => '🔙 Назад в настройки', 'callback_data' => 'settings_menu']]
            ]
        ]);

        Telegram::editMessageText([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard,
        ]);

        return true;
    }

    /**
     * ✅ Handle timezone selection
     */
    public static function handleTimezoneSelection($data, $user, $chatId, $messageId)
    {
        $timezone = str_replace('settings_timezone_', '', $data);

        // Update user's timezone
        $user->timezone = $timezone;
        $user->save();

        $timezoneSafe = str_replace('_', ' ', $timezone);

        // Confirm selection to the user
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "✅ Your timezone has been updated to: `{$timezoneSafe}`",
            'parse_mode' => 'Markdown',
        ]);

        Telegram::deleteMessage([
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);

        // Return to the main settings menu
        self::showMainMenu($chatId, $user);

        return true;
    }


    /**
     * ✅ Show edit name form
     */
    public static function showEditNameForm($chatId, $user, $messageId)
    {
        // помечаем, что для этого юзера ждём ввод имени (5 минут)
        cache()->put("tg_{$user->telegram_id}_editing_name", true, now()->addMinutes(5));

        Telegram::editMessageText([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => '👤 *EDIT NAME*' . "\n\n" .
                    "Current: " . ($user->first_name ?? 'Not specified') . "\n\n" .
                    "Enter new name:",
            'parse_mode' => 'Markdown',
        ]);
    }

    /**
     * ✅ Update user's first name
     */
    public static function updateUserFirstName($user, $newName, $chatId)
    {
        try {
            $user->update(['first_name' => $newName]);

            Log::info('User first name updated', [
                'telegramId' => $user->telegram_id,
                'newName'    => $newName,
            ]);

            Telegram::sendMessage([
                'chat_id'    => $chatId,
                'text'       => '✅ *Name updated:* ' . $newName,
                'parse_mode' => 'Markdown',
            ]);

            self::showMainMenu($chatId, $user);
            return true;
        } catch (\Exception $e) {
            Log::error('Error updating first name', [
                'telegramId' => $user->telegram_id,
                'error'      => $e->getMessage(),
            ]);

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text'    => '❌ Error updating name. Please try again later.',
            ]);

            return false;
        }
    }

    public static function showHelpInfo($chatId, $messageId)
    {
        $helpText = "❓ *Help Information*\n\n" .
                    "Here you can adjust your settings:\n" .
                    "• Change your timezone to ensure correct time tracking.\n" .
                    "• Edit your display name used in the bot.\n\n" .
                    "Use the buttons below to navigate through the settings.";

        Telegram::editMessageText([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $helpText,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => '🔙 Back to Settings', 'callback_data' => 'settings_menu']],
                ],
            ]),
        ]);

        return true;
    }
}
