<?php

namespace IVal\SiligramBot;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Http;
use IVal\SiligramBot\Exceptions\TelegramApiException;

class TelegramClient
{
    /**
     * The Telegram Bot token.
     */
    protected string $token;

    /**
     * Configuration options.
     */
    protected array $config;

    /**
     * Base URL for Telegram Bot API.
     */
    protected string $baseUrl = 'https://api.telegram.org';

    /**
     * Create a new TelegramClient instance.
     */
    public function __construct(?string $token = null, array $config = [])
    {
        $this->config = $config;
        $this->token = $token ?? $config['token'] ?? (string) config('telegram.token', '');
    }

    /**
     * Switch bot client instance using a configured named bot.
     *
     * @param string $name Bot identifier configured in config/telegram.php (e.g. 'default', 'alerts')
     * @return self
     * @throws \InvalidArgumentException
     */
    public function bot(string $name): self
    {
        $bots = $this->config['bots'] ?? config('telegram.bots', []);

        if (empty($bots[$name])) {
            throw new \InvalidArgumentException(
                "Telegram bot [{$name}] is not configured. Define its token in your .env file and map it in config/telegram.php."
            );
        }

        return new self($bots[$name], $this->config);
    }

    /**
     * Get the current bot token.
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Send a text message to a chat.
     *
     * @param int|string $chatId
     * @param string $text
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws TelegramApiException
     */
    public function sendMessage(int|string $chatId, string $text, array $options = []): array
    {
        $payload = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $this->config['parse_mode'] ?? config('telegram.parse_mode', 'HTML'),
        ], $options);

        return $this->call('sendMessage', $payload);
    }

    /**
     * Send a photo to a chat.
     *
     * @param int|string $chatId
     * @param string $photo URL, file_id, or local path
     * @param string|null $caption
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws TelegramApiException
     */
    public function sendPhoto(int|string $chatId, string $photo, ?string $caption = null, array $options = []): array
    {
        $payload = array_merge([
            'chat_id' => $chatId,
            'photo' => $photo,
        ], $options);

        if ($caption !== null) {
            $payload['caption'] = $caption;
            $payload['parse_mode'] = $payload['parse_mode'] ?? $this->config['parse_mode'] ?? config('telegram.parse_mode', 'HTML');
        }

        return $this->call('sendPhoto', $payload);
    }

    /**
     * Send a document/file to a chat.
     *
     * @param int|string $chatId
     * @param string $document URL, file_id, or local path
     * @param string|null $caption
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws TelegramApiException
     */
    public function sendDocument(int|string $chatId, string $document, ?string $caption = null, array $options = []): array
    {
        $payload = array_merge([
            'chat_id' => $chatId,
            'document' => $document,
        ], $options);

        if ($caption !== null) {
            $payload['caption'] = $caption;
            $payload['parse_mode'] = $payload['parse_mode'] ?? $this->config['parse_mode'] ?? config('telegram.parse_mode', 'HTML');
        }

        return $this->call('sendDocument', $payload);
    }

    /**
     * Edit an existing message text.
     *
     * @param int|string $chatId
     * @param int $messageId
     * @param string $text
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws TelegramApiException
     */
    public function editMessageText(int|string $chatId, int $messageId, string $text, array $options = []): array
    {
        $payload = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => $this->config['parse_mode'] ?? config('telegram.parse_mode', 'HTML'),
        ], $options);

        return $this->call('editMessageText', $payload);
    }

    /**
     * Answer an inline keyboard callback query.
     *
     * @param string $callbackQueryId
     * @param string|null $text
     * @param bool $showAlert
     * @param array<string, mixed> $options
     * @return mixed
     * @throws TelegramApiException
     */
    public function answerCallbackQuery(
        string $callbackQueryId,
        ?string $text = null,
        bool $showAlert = false,
        array $options = []
    ): mixed {
        $payload = array_merge([
            'callback_query_id' => $callbackQueryId,
            'show_alert' => $showAlert,
        ], $options);

        if ($text !== null) {
            $payload['text'] = $text;
        }

        return $this->call('answerCallbackQuery', $payload);
    }

    /**
     * Register a webhook endpoint with Telegram.
     *
     * @param string $url
     * @param array<string, mixed> $options
     * @return mixed
     * @throws TelegramApiException
     */
    public function setWebhook(string $url, array $options = []): mixed
    {
        $payload = array_merge([
            'url' => $url,
        ], $options);

        return $this->call('setWebhook', $payload);
    }

    /**
     * Remove the currently registered webhook.
     *
     * @param bool $dropPendingUpdates
     * @return mixed
     * @throws TelegramApiException
     */
    public function deleteWebhook(bool $dropPendingUpdates = false): mixed
    {
        return $this->call('deleteWebhook', [
            'drop_pending_updates' => $dropPendingUpdates,
        ]);
    }

    /**
     * Retrieve the current webhook status.
     *
     * @return array<string, mixed>
     * @throws TelegramApiException
     */
    public function getWebhookInfo(): array
    {
        return $this->call('getWebhookInfo');
    }

    /**
     * Get basic information about the bot.
     *
     * @return array<string, mixed>
     * @throws TelegramApiException
     */
    public function getMe(): array
    {
        return $this->call('getMe');
    }

    /**
     * Low-level caller for any Telegram Bot API endpoint.
     *
     * @param string $method
     * @param array<string, mixed> $payload
     * @return mixed
     * @throws TelegramApiException
     */
    public function call(string $method, array $payload = []): mixed
    {
        if (empty($this->token)) {
            throw new TelegramApiException(
                'Telegram Bot token is not configured. Please define TELEGRAM_BOT_TOKEN in your .env file.'
            );
        }

        $timeout = $this->config['timeout'] ?? (int) config('telegram.timeout', 10);
        $endpoint = "{$this->baseUrl}/bot{$this->token}/{$method}";

        $preparedPayload = $this->preparePayload($payload);

        // Check if any payload parameter is a local file that requires multipart upload
        $fileKey = $this->detectLocalFileKey($preparedPayload);

        if ($fileKey !== null) {
            $filePath = $preparedPayload[$fileKey];
            unset($preparedPayload[$fileKey]);

            $request = Http::timeout($timeout)->asMultipart();
            foreach ($preparedPayload as $k => $v) {
                $request->attach($k, is_array($v) ? json_encode($v) : (string) $v);
            }
            $request->attach($fileKey, file_get_contents($filePath), basename($filePath));
            $response = $request->post($endpoint);
        } else {
            $response = Http::timeout($timeout)
                ->asJson()
                ->post($endpoint, $preparedPayload);
        }

        $body = $response->json();

        if (!$response->successful() || !is_array($body) || ($body['ok'] ?? false) !== true) {
            throw TelegramApiException::fromResponse(
                $response->status(),
                is_array($body) ? $body : ['description' => $response->body()]
            );
        }

        return $body['result'] ?? null;
    }

    /**
     * Normalize payload values (e.g. convert Arrayable to array).
     */
    protected function preparePayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if ($value instanceof Arrayable) {
                $payload[$key] = $value->toArray();
            }
        }

        return $payload;
    }

    /**
     * Detect if any payload value points to an existing readable local file.
     */
    protected function detectLocalFileKey(array $payload): ?string
    {
        $candidateKeys = ['photo', 'document', 'audio', 'video', 'voice', 'certificate'];

        foreach ($candidateKeys as $key) {
            if (isset($payload[$key]) && is_string($payload[$key])) {
                $val = $payload[$key];
                // Check if it's not a URL and exists locally on disk
                if (!str_starts_with($val, 'http://') && !str_starts_with($val, 'https://') && @is_file($val)) {
                    return $key;
                }
            }
        }

        return null;
    }
}
