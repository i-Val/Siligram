<?php

namespace IVal\SiligramBot\Tests\Feature;

use Illuminate\Support\Facades\Http;
use IVal\SiligramBot\Tests\TestCase;

class ArtisanCommandsTest extends TestCase
{
    public function test_telegram_webhook_set_command(): void
    {
        Http::fake([
            'https://api.telegram.org/bot123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ/setWebhook' => Http::response([
                'ok' => true,
                'result' => true,
                'description' => 'Webhook was set',
            ], 200),
        ]);

        $this->artisan('telegram:webhook:set', [
            '--url' => 'https://example.com/api/telegram/webhook',
            '--secret' => 'my-custom-secret',
        ])
            ->expectsOutputToContain('Webhook set successfully!')
            ->assertExitCode(0);

        Http::assertSent(function ($request) {
            return $request['url'] === 'https://example.com/api/telegram/webhook'
                && $request['secret_token'] === 'my-custom-secret';
        });
    }

    public function test_telegram_webhook_delete_command(): void
    {
        Http::fake([
            'https://api.telegram.org/bot123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ/deleteWebhook' => Http::response([
                'ok' => true,
                'result' => true,
                'description' => 'Webhook was deleted',
            ], 200),
        ]);

        $this->artisan('telegram:webhook:delete', ['--drop-pending-updates' => true])
            ->expectsOutputToContain('Webhook removed successfully.')
            ->assertExitCode(0);

        Http::assertSent(function ($request) {
            return $request['drop_pending_updates'] === true;
        });
    }

    public function test_telegram_webhook_info_command(): void
    {
        Http::fake([
            'https://api.telegram.org/bot123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ/getWebhookInfo' => Http::response([
                'ok' => true,
                'result' => [
                    'url' => 'https://example.com/api/telegram/webhook',
                    'has_custom_certificate' => false,
                    'pending_update_count' => 0,
                ],
            ], 200),
        ]);

        $this->artisan('telegram:webhook:info')
            ->assertExitCode(0);
    }
}
