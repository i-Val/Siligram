<?php

namespace IVal\SiligramBot\Tests\Feature;

use Illuminate\Support\Facades\Event;
use IVal\SiligramBot\Events\TelegramUpdateReceived;
use IVal\SiligramBot\Tests\TestCase;

class WebhookSecurityTest extends TestCase
{
    public function test_it_accepts_webhook_with_valid_secret_header(): void
    {
        Event::fake([TelegramUpdateReceived::class]);

        $payload = [
            'update_id' => 123456,
            'message' => [
                'message_id' => 1,
                'chat' => ['id' => 777],
                'text' => 'Hello',
            ],
        ];

        $response = $this->withHeaders([
            'X-Telegram-Bot-Api-Secret-Token' => 'secret-test-token',
        ])->postJson('/api/telegram/webhook', $payload);

        $response->assertStatus(200)
            ->assertExactJson(['ok' => true]);

        Event::assertDispatched(TelegramUpdateReceived::class, function ($event) {
            return $event->update->updateId() === 123456
                && $event->update->text() === 'Hello';
        });
    }

    public function test_it_rejects_webhook_with_invalid_secret_header(): void
    {
        Event::fake([TelegramUpdateReceived::class]);

        $response = $this->withHeaders([
            'X-Telegram-Bot-Api-Secret-Token' => 'wrong-secret',
        ])->postJson('/api/telegram/webhook', [
            'update_id' => 123456,
        ]);

        $response->assertStatus(403);
        Event::assertNotDispatched(TelegramUpdateReceived::class);
    }

    public function test_it_rejects_webhook_with_missing_secret_header_when_secret_is_configured(): void
    {
        Event::fake([TelegramUpdateReceived::class]);

        $response = $this->postJson('/api/telegram/webhook', [
            'update_id' => 123456,
        ]);

        $response->assertStatus(403);
        Event::assertNotDispatched(TelegramUpdateReceived::class);
    }
}
