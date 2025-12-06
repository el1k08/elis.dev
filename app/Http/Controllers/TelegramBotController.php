<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use App\Services\TelegramMenuService;
use App\Modules\Worker\Services\WorkerButtonHandler;
use App\Modules\Worker\Services\WorkerMenuService;
use App\Modules\Worker\Services\ShiftService;
use App\Models\TelegramUser;
use App\Modules\Settings\Services\SettingsMenuService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\TelegramSettingsController;

class TelegramBotController extends Controller
{
    public function webhook(Request $request)
    {
        try {
            $update = json_decode($request->getContent(), true);

            Log::info('Webhook received', ['updateId' => $update['update_id'] ?? null]);

            // 1. Обробка текстових повідомлень
            if (isset($update['message']['text'])) {
                $text = $update['message']['text'];
                $chatId = $update['message']['chat']['id'];
                $telegramId = $update['message']['from']['id'];
                $firstName = $update['message']['from']['first_name'] ?? null;
                $lastName = $update['message']['from']['last_name'] ?? null;
                $username = $update['message']['from']['username'] ?? null;
                $languageCode = $update['message']['from']['language_code'] ?? 'en';

                Log::info('Message received', [
                    'text' => $text,
                    'chatId' => $chatId,
                    'telegramId' => $telegramId,
                ]);

                // ✅ ПЕРШИЙ ЗАПУСК
                if ($text === '/start') {

                    error_log('Start command received for chatId: ' . $chatId);

                    $user = TelegramUser::getOrCreate($telegramId, $chatId, [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'username' => $username,
                        'language_code' => $languageCode,
                    ]);

                    Log::info('User created/updated', [
                        'telegramId' => $telegramId,
                        'userId' => $user->id,
                    ]);

                    $timezoneSafe = str_replace('_', ' ', $user->timezone);

                    $text = '✅ *Welcome!*' . "\n\n" .
                                'Your timezone: ' . $timezoneSafe . "\n\n" .
                                'Language: ' . $languageCode . "\n\n" .
                                'You can change settings in the menu ⚙️ Settings.';

                    error_log('Start called for chatId: ' . $chatId . ' and timezone: ' . $user->timezone);
                    TelegramMenuService::showMainMenu($chatId, $text);
                    return response()->json(['status' => 'ok']);
                }

                // ✅ Отримуємо користувача для всіх інших команд
                $user = TelegramUser::findByTelegramId($telegramId);

                if (!$user) {
                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => '⚠️ Please start with /start',
                    ]);
                    return response()->json(['status' => 'ok']);
                }

                // ✅ Перевіряємо статус
                if ($user->status === 'blocked') {
                    Log::warning('Blocked user tried to use bot', ['telegramId' => $telegramId]);
                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => '🚫 Your account is blocked.',
                    ]);
                    return response()->json(['status' => 'ok']);
                }

                if ($user->status !== 'active') {
                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => '⚠️ Your account is inactive.',
                    ]);
                    return response()->json(['status' => 'ok']);
                }

                // Оновлюємо остання активність
                $user->update(['last_activity_at' => now()]);

                // ✅ ОБРОБЛЯЄМО КОМАНДИ

                $editingKey = "tg_{$telegramId}_editing_name";
                if (cache()->has($editingKey)) {
                    cache()->forget($editingKey); // сбрасываем флаг, чтобы не ловить каждое сообщение

                    // простая валидация
                    if ($text === '' || mb_strlen($text) > 64) {
                        Telegram::sendMessage([
                            'chat_id' => $chatId,
                            'text'    => '❌ Некоректне ім\'я. Спробуйте ще раз, не більше 64 символів.',
                        ]);

                        // снова просим ввести имя
                        SettingsMenuService::showEditNameForm($chatId, $user);
                        return response()->json(['status' => 'ok']);
                    }

                    // 2) обновляем имя через сервис
                    SettingsMenuService::updateUserFirstName($user, $text, $chatId);
                    return response()->json(['status' => 'ok']);
                }

                // 👷 Worker модуль
                if ($text === '👷 Worker') {
                    Log::info('Worker module selected');
                    WorkerMenuService::showMainMenu($chatId);
                    return response()->json(['status' => 'ok']);
                }

                // 💰 Financing модуль
                if ($text === '💰 Financing') {
                    Log::info('Financing module selected');
                    Telegram::sendMessage([
                        'chat_id' => $chatId,
                        'text' => '💰 *Financing Module*' . "\n\n" . 'Coming soon...',
                        'parse_mode' => 'Markdown'
                    ]);
                    return response()->json(['status' => 'ok']);
                }

                //⚙️ Settings - ✅ ВИПРАВЛЕНО: передаємо $user замість $telegramId
                if ($text === '⚙️ Settings') {
                    Log::info('Settings opened', ['telegramId' => $telegramId]);
                    Telegram::deleteMessage([
                        'chat_id' => $chatId,
                        'message_id' => $update['message']['message_id'],
                    ]);
                    SettingsMenuService::showMainMenu($chatId, $user, null, $firstName);
                    return response()->json(['status' => 'ok']);
                }

                // Кнопка "Назад"
                if ($text === '⬅️ Назад' || $text === '⬅️ Back') {
                    Log::info('Back to main menu');
                    TelegramMenuService::showMainMenu($chatId, null, null, $firstName);
                    return response()->json(['status' => 'ok']);
                }

                // ✅ Обробка Worker кнопок
                $workerHandled = WorkerButtonHandler::handle($text, $chatId, $telegramId, $user);
                if ($workerHandled) {
                    Log::info('Worker button handled', ['button' => $text]);
                    return response()->json(['status' => 'ok']);
                }

                // Невідома команда
                Log::info('Unknown command', ['text' => $text, 'telegramId' => $telegramId]);
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => '❓ Unknown command. Try again.',
                ]);

                return response()->json(['status' => 'ok']);
            }

            // 2. Обробка callback_query
            if(isset($update['callback_query'])) {

                $data = $update['callback_query']['data'];
                $chatId = $update['callback_query']['message']['chat']['id'];
                $telegramId = $update['callback_query']['from']['id'];
                $messageId = $update['callback_query']['message']['message_id'];

                if(Str::startsWith($data, 'settings_')) {
                    $user = TelegramUser::findByTelegramId($telegramId);
                    app(\App\Http\Controllers\TelegramSettingsController::class)
                        ->handleCallback($data, $chatId, $telegramId, $messageId, $user);
                    return response()->json(['status' => 'ok']);
                }

                if(Str::startsWith($data, 'worker_')) {
                    $user = TelegramUser::findByTelegramId($telegramId);
                    WorkerButtonHandler::callbackHandler($data, $chatId, $telegramId, $messageId, $user);
                    return response()->json(['status' => 'ok']);
                }

                Telegram::answerCallbackQuery([
                    'callback_query_id' => $update['callback_query']['id'],
                    'text' => '⚠️ Unknown action.',
                    'show_alert' => false,
                ]);
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('Webhook Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json(['status' => 'ok']);  // ✅ Завжди 200 OK
        }
    }
}
