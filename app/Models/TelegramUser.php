<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TelegramUser extends Model
{
    use HasFactory;

    protected $table = 'telegram_users';

    protected $fillable = [
        'telegram_id',
        'chat_id',
        'first_name',
        'last_name',
        'username',
        'language_code',
        'timezone',
        'status',
        'notes',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    /**
     * ✅ Отримати або створити користувача
     */
    public static function getOrCreate($telegramId, $chatId = null, $data = [])
    {
        $user = self::where('telegram_id', $telegramId)->first();

        if ($user) {
            // ✅ Оновлюємо last_activity_at та chat_id
            $user->update([
                'chat_id' => $chatId ?? $user->chat_id,
                'last_activity_at' => now(),
            ]);
            return $user;
        }

        // ✅ Створюємо нового користувача
        return self::create([
            'telegram_id' => $telegramId,
            'chat_id' => $chatId,
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'username' => $data['username'] ?? null,
            'language_code' => $data['language_code'] ?? 'uk',
            'timezone' => $data['timezone'] ?? self::getTimezoneByLanguage($data['language_code'] ?? 'uk'),
            'status' => 'active',
            'last_activity_at' => now(),
        ]);
    }

    /**
     * ✅ Знайти користувача за Telegram ID
     */
    public static function findByTelegramId($telegramId)
    {
        return self::where('telegram_id', $telegramId)->first();
    }

    /**
     * ✅ Визначити часовий пояс за мовою
     */
    private static function getTimezoneByLanguage($languageCode)
    {
        $timezoneMap = [
            'uk' => 'Europe/Kyiv',
            'ru' => 'Europe/Moscow',
            'pl' => 'Europe/Warsaw',
            'en' => 'Europe/London',
            'de' => 'Europe/Berlin',
            'fr' => 'Europe/Paris',
            'es' => 'Europe/Madrid',
            'it' => 'Europe/Rome',
        ];

        return $timezoneMap[$languageCode] ?? 'Europe/Kyiv';
    }

    /**
     * ✅ Оновити часовий пояс
     */
    public function updateTimezone($timezone)
    {
        $this->update(['timezone' => $timezone]);
        return $this;
    }

    /**
     * ✅ Отримати смени користувача
     */
    public function shifts()
    {
        return \App\Modules\Worker\Models\Shift::where('telegram_id', $this->telegram_id);
    }

    /**
     * ✅ Отримати статистику
     */
    public function getStats($startDate = null, $endDate = null)
    {
        return \App\Modules\Worker\Models\Shift::getUserStats(
            $this->telegram_id,
            $startDate,
            $endDate
        );
    }

    /**
     * ✅ Отримати активну смену
     */
    public function getActiveShift()
    {
        return \App\Modules\Worker\Models\Shift::where('telegram_id', $this->telegram_id)
            ->whereNull('end_time')
            ->first();
    }

    /**
     * ✅ Перевірити активну смену
     */
    public function hasActiveShift()
    {
        return $this->getActiveShift() !== null;
    }
}
