<?php

namespace IVal\SiligramBot\Commands;

use Illuminate\Console\Command;
use IVal\SiligramBot\Exceptions\TelegramApiException;
use IVal\SiligramBot\TelegramClient;

class DeleteWebhookCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:webhook:delete
                            {--bot= : Name of the bot defined in config (defaults to default bot)}
                            {--drop-pending-updates : Drop all pending updates upon webhook removal}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Remove the registered Telegram webhook';

    /**
     * Execute the console command.
     */
    public function handle(TelegramClient $client): int
    {
        $bot = $this->option('bot');
        if ($bot) {
            $client = $client->bot($bot);
        }

        $drop = (bool) $this->option('drop-pending-updates');

        $this->info('Removing Telegram webhook...');

        try {
            $response = $client->deleteWebhook($drop);

            $this->info('Webhook removed successfully.');
            if (is_array($response) && isset($response['description'])) {
                $this->line("Telegram Response: {$response['description']}");
            }

            return self::SUCCESS;
        } catch (TelegramApiException $e) {
            $this->error("Failed to remove webhook: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
