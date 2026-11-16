<?php

namespace IVal\SiligramBot\Commands;

use Illuminate\Console\Command;
use IVal\SiligramBot\Exceptions\TelegramApiException;
use IVal\SiligramBot\TelegramClient;

class SetWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:webhook:set
                            {--url= : Full webhook URL (defaults to app URL + configured webhook path)}
                            {--secret= : Secret token for X-Telegram-Bot-Api-Secret-Token validation}
                            {--bot= : Name of the bot defined in config (defaults to default bot)}
                            {--drop-pending-updates : Drop all pending updates when setting webhook}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register the webhook endpoint with Telegram Bot API';

    /**
     * Execute the console command.
     */
    public function handle(TelegramClient $client): int
    {
        $bot = $this->option('bot');
        if ($bot) {
            $client = $client->bot($bot);
        }

        $url = $this->option('url');
        if (!$url) {
            $path = config('telegram.webhook.path', 'api/telegram/webhook');
            if ($bot && $bot !== 'default') {
                $path = rtrim($path, '/') . '/' . $bot;
            }
            $url = url($path);
        }

        if (!str_starts_with($url, 'https://')) {
            $this->warn('Warning: Telegram requires webhooks to use HTTPS with a valid SSL/TLS certificate.');
        }

        $secret = $this->option('secret') ?? config('telegram.webhook.secret');

        $options = [];
        if (!empty($secret)) {
            $options['secret_token'] = $secret;
        }

        if ($this->option('drop-pending-updates')) {
            $options['drop_pending_updates'] = true;
        }

        $this->info("Registering webhook: {$url}");

        try {
            $response = $client->setWebhook($url, $options);

            $this->info('Webhook set successfully!');
            if (is_array($response) && isset($response['description'])) {
                $this->line("Telegram Response: {$response['description']}");
            }

            return self::SUCCESS;
        } catch (TelegramApiException $e) {
            $this->error("Failed to set webhook: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
