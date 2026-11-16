<?php

namespace IVal\SiligramBot\Tests\Unit;

use IVal\SiligramBot\DTOs\Update;
use PHPUnit\Framework\TestCase;

class UpdateDtoTest extends TestCase
{
    public function test_it_parses_standard_message(): void
    {
        $payload = [
            'update_id' => 10001,
            'message' => [
                'message_id' => 50,
                'chat' => [
                    'id' => 999888,
                    'type' => 'private',
                ],
                'from' => [
                    'id' => 999888,
                    'is_bot' => false,
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'username' => 'johndoe',
                ],
                'text' => '/start welcome',
            ],
        ];

        $update = Update::fromArray($payload);

        $this->assertSame(10001, $update->updateId());
        $this->assertSame(50, $update->messageId());
        $this->assertSame(999888, $update->chatId());
        $this->assertSame(999888, $update->fromId());
        $this->assertSame('johndoe', $update->username());
        $this->assertSame('John', $update->firstName());
        $this->assertSame('Doe', $update->lastName());
        $this->assertSame('/start welcome', $update->text());
        $this->assertTrue($update->isCommand());
        $this->assertSame('start', $update->command());
        $this->assertSame('welcome', $update->commandArguments());
        $this->assertFalse($update->isCallbackQuery());
    }

    public function test_it_parses_command_with_bot_username(): void
    {
        $payload = [
            'update_id' => 10002,
            'message' => [
                'chat' => ['id' => 12345],
                'text' => '/status@SiligramBot full report',
            ],
        ];

        $update = Update::fromArray($payload);

        $this->assertTrue($update->isCommand());
        $this->assertSame('status', $update->command());
        $this->assertSame('full report', $update->commandArguments());
    }

    public function test_it_parses_callback_query(): void
    {
        $payload = [
            'update_id' => 10003,
            'callback_query' => [
                'id' => 'cb_query_12345',
                'from' => [
                    'id' => 555444,
                    'username' => 'alice',
                ],
                'message' => [
                    'message_id' => 88,
                    'chat' => [
                        'id' => 555444,
                    ],
                ],
                'data' => 'action:confirm',
            ],
        ];

        $update = Update::fromArray($payload);

        $this->assertTrue($update->isCallbackQuery());
        $this->assertSame('cb_query_12345', $update->callbackQueryId());
        $this->assertSame('action:confirm', $update->callbackData());
        $this->assertSame(555444, $update->chatId());
        $this->assertSame('alice', $update->username());
    }
}
