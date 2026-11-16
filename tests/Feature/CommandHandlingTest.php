<?php

namespace IVal\SiligramBot\Tests\Feature;

use IVal\SiligramBot\Attributes\OnCommand;
use IVal\SiligramBot\Contracts\CommandHandler;
use IVal\SiligramBot\DTOs\Update;
use IVal\SiligramBot\Support\CommandRegistry;
use IVal\SiligramBot\Tests\TestCase;

class TestStartHandler implements CommandHandler
{
    public static bool $executed = false;
    public static ?string $capturedArgs = null;

    public function handle(Update $update): mixed
    {
        self::$executed = true;
        self::$capturedArgs = $update->commandArguments();

        return null;
    }
}

#[OnCommand(['ping', 'status'])]
class TestAttributeHandler implements CommandHandler
{
    public static int $calls = 0;

    public function handle(Update $update): mixed
    {
        self::$calls++;

        return null;
    }
}

class CommandHandlingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        TestStartHandler::$executed = false;
        TestStartHandler::$capturedArgs = null;
        TestAttributeHandler::$calls = 0;
    }

    public function test_it_executes_registered_command_handler(): void
    {
        $registry = $this->app->make(CommandRegistry::class);
        $registry->register('start', TestStartHandler::class);

        $payload = [
            'update_id' => 12345,
            'message' => [
                'message_id' => 1,
                'chat' => ['id' => 999],
                'text' => '/start referral_123',
            ],
        ];

        $response = $this->withHeaders([
            'X-Telegram-Bot-Api-Secret-Token' => 'secret-test-token',
        ])->postJson('/api/telegram/webhook', $payload);

        $response->assertStatus(200);
        $this->assertTrue(TestStartHandler::$executed);
        $this->assertSame('referral_123', TestStartHandler::$capturedArgs);
    }

    public function test_it_executes_command_handler_via_attribute(): void
    {
        $registry = $this->app->make(CommandRegistry::class);
        $registry->registerClass(TestAttributeHandler::class);

        $this->withHeaders([
            'X-Telegram-Bot-Api-Secret-Token' => 'secret-test-token',
        ])->postJson('/api/telegram/webhook', [
            'update_id' => 12346,
            'message' => [
                'chat' => ['id' => 999],
                'text' => '/ping',
            ],
        ]);

        $this->assertSame(1, TestAttributeHandler::$calls);

        $this->withHeaders([
            'X-Telegram-Bot-Api-Secret-Token' => 'secret-test-token',
        ])->postJson('/api/telegram/webhook', [
            'update_id' => 12347,
            'message' => [
                'chat' => ['id' => 999],
                'text' => '/status',
            ],
        ]);

        $this->assertSame(2, TestAttributeHandler::$calls);
    }
}
