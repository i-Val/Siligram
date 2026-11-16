<?php

namespace IVal\SiligramBot\Commands;

use Illuminate\Console\Command;
use IVal\SiligramBot\Exceptions\TelegramApiException;
use IVal\SiligramBot\TelegramClient;

class WebhookInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:webhook:info
                            {--bot= : Name of the bot defined in config (defaults to default bot)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inspect current Telegram webhook status';

    /**
     * Execute the console command.
     */
    public function handle(TelegramClient $client): int
    {
        $bot = $this->option('bot');
        if ($bot) {
            $client = $client->bot($bot);
        }

        try {
            $info = $client->getWebhookInfo();

            $rows = [];
            foreach ($info as $key => $value) {
                if (is_bool($value)) {
                    $formattedValue = $value ? 'Yes' : 'No';
                } elseif (is_array($value)) {
                    $formattedValue = json_encode($value);
                } elseif (str_contains($key, 'date') && is_numeric($value) && $value > 0) {
                    $formattedValue = date('Y-m-d H:i:s', $value) . " ({$value})";
                } else {
                    $formattedValue = (string) $value;
                }

                $rows[] = [$key, $formattedValue];
            }

            $this->table(['Property', 'Value'], $rows);

            return self::SUCCESS;
        } catch (TelegramApiException $e) {
            $this->error("Failed to retrieve webhook info: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
