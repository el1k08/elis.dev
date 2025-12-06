<?php

namespace App\Http\Controllers;

use App\Models\TelegramUser;
use App\Modules\Settings\Services\SettingsMenuService;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Log;
use App\Services\TelegramMenuService;

class TelegramSettingsController extends Controller
{
    /**
     * ✅ Обработка всех settings callbacks
     */
    public function handleCallback($data, $chatId, $telegramId, $messageId, $user)
    {
        Log::info('SettingsController::handleCallback', ['data' => $data, 'telegramId' => $telegramId]);

        // ✅ Главное меню settings
        if ($data === 'settings_menu') {
            SettingsMenuService::showMainMenu($chatId, $user, $messageId);
            return true;
        }

        // ✅ Выбор часового пояса
        if ($data === 'settings_timezone') {
            SettingsMenuService::showTimezoneMenu($chatId, $user, $messageId);
            return true;
        }

        // ✅ Обработка часовых поясов (tz_kyiv, tz_warsaw и т.д.)
        if (strpos($data, 'settings_timezone_') === 0) {
            SettingsMenuService::handleTimezoneSelection($data, $user, $chatId, $messageId);
            return true;
        }

        // ✅ Форма изменения имени
        if ($data === 'settings_edit_name') {
            SettingsMenuService::showEditNameForm($chatId, $user, $messageId);
            return true;
        }

        if ($data === 'settings_help') {
            SettingsMenuService::showHelpInfo($chatId, $messageId);
            return true;
        }

        // ✅ Возврат в главное меню
        if( $data === 'settings_back') {
            TelegramMenuService::showMainMenu($chatId, false, $messageId, $user->first_name ?? 'User');
            return true;
        }

        // ✅ Неизвестный callback
        Log::warning('Unknown settings callback', ['data' => $data]);
        return false;
    }
}
