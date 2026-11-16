<?php

namespace IVal\SiligramBot\Tests\Feature;

use Illuminate\Support\Facades\Http;
use IVal\SiligramBot\Exceptions\TelegramApiException;
use IVal\SiligramBot\Facades\Telegram;
use IVal\SiligramBot\Support\InlineKeyboard;
use IVal\SiligramBot\Tests\TestCase;

class SendMessageTest extends TestCase
{
    public function test_it_sends_a_message_successfully(): void
    {
        Http::fake([
            'https://api.telegram.org/bot123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ/sendMessage' => Http::response([
                'ok' => true,
                'result' => [
                    'message_id' => 101,
                    'chat' => ['id' => 987654321],
                    'text' => 'Hello World!',
                ],
            ], 200),
        ]);

        $result = Telegram::sendMessage(987654321, 'Hello World!');

        $this->assertSame(101, $result['message_id']);
        $this->assertSame('Hello World!', $result['text']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.telegram.org/bot123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ/sendMessage'
                && $request['chat_id'] === 987654321
                && $request['text'] === 'Hello World!'
                && $request['parse_mode'] === 'HTML';
        });
    }

    public function test_it_sends_message_with_inline_keyboard(): void
    {
        Http::fake([
            'https://api.telegram.org/bot123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ/sendMessage' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 102],
            ], 200),
        ]);

        $keyboard = InlineKeyboard::make()->row([
            InlineKeyboard::button('Click Me', 'btn:click'),
        ]);

        Telegram::sendMessage(987654321, 'Choose:', [
            'reply_markup' => $keyboard,
        ]);

        Http::assertSent(function ($request) {
            return isset($request['reply_markup']['inline_keyboard'])
                && $request['reply_markup']['inline_keyboard'][0][0]['text'] === 'Click Me';
        });
    }

    public function test_it_switches_bot_token(): void
    {
        Http::fake([
            'https://api.telegram.org/bot987654321:XYZ-alerts-token/sendMessage' => Http::response([
                'ok' => true,
                'result' => ['message_id' => 103],
            ], 200),
        ]);

        Telegram::bot('alerts')->sendMessage(987654321, 'Alert triggered!');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '987654321:XYZ-alerts-token');
        });
    }

    public function test_it_throws_telegram_api_exception_on_failure(): void
    {
        Http::fake([
            'https://api.telegram.org/bot123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ/sendMessage' => Http::response([
                'ok' => false,
                'error_code' => 400,
                'description' => 'Bad Request: chat not found',
            ], 400),
        ]);

        $this->expectException(TelegramApiException::class);
        $this->expectExceptionMessage('Telegram API Error [400]: Bad Request: chat not found');

        Telegram::sendMessage(999999, 'Invalid chat test');
    }
}
