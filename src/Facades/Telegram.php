<?php

namespace IVal\SiligramBot\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \IVal\SiligramBot\TelegramClient bot(string $name)
 * @method static string getToken()
 * @method static array sendMessage(int|string $chatId, string $text, array $options = [])
 * @method static array sendPhoto(int|string $chatId, string $photo, ?string $caption = null, array $options = [])
 * @method static array sendDocument(int|string $chatId, string $document, ?string $caption = null, array $options = [])
 * @method static array editMessageText(int|string $chatId, int $messageId, string $text, array $options = [])
 * @method static array answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false, array $options = [])
 * @method static array setWebhook(string $url, array $options = [])
 * @method static array deleteWebhook(bool $dropPendingUpdates = false)
 * @method static array getWebhookInfo()
 * @method static array getMe()
 * @method static array call(string $method, array $payload = [])
 *
 * @see \IVal\SiligramBot\TelegramClient
 */
class Telegram extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'telegram';
    }
}
