<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TelegramBotController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Вебхук для отримання оновлень від Telegram (обов'язково HTTPS)
Route::post('/telegram/webhook', [TelegramBotController::class, 'webhook']);

// Допоміжні роути (для налаштування та тестування)
Route::get('/telegram/test', [TelegramBotController::class, 'sendTestMessage']);
Route::get('/telegram/info', [TelegramBotController::class, 'getBotInfo']);
Route::post('/telegram/set-webhook', [TelegramBotController::class, 'setWebhook']);
Route::get('/telegram/webhook-info', [TelegramBotController::class, 'getWebhookInfo']);
