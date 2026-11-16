<?php

namespace IVal\SiligramBot\Tests\Unit;

use IVal\SiligramBot\Support\InlineKeyboard;
use PHPUnit\Framework\TestCase;

class InlineKeyboardTest extends TestCase
{
    public function test_it_builds_rows_and_buttons_correctly(): void
    {
        $keyboard = InlineKeyboard::make()
            ->row([
                InlineKeyboard::button('Approve', 'order:approve:42'),
                InlineKeyboard::button('Reject', 'order:reject:42'),
            ])
            ->row([
                InlineKeyboard::url('View Dashboard', 'https://example.com/orders/42'),
            ]);

        $expected = [
            'inline_keyboard' => [
                [
                    ['text' => 'Approve', 'callback_data' => 'order:approve:42'],
                    ['text' => 'Reject', 'callback_data' => 'order:reject:42'],
                ],
                [
                    ['text' => 'View Dashboard', 'url' => 'https://example.com/orders/42'],
                ],
            ],
        ];

        $this->assertSame($expected, $keyboard->toArray());
        $this->assertJsonStringEqualsJsonString(json_encode($expected), $keyboard->toJson());
    }

    public function test_it_creates_special_button_types(): void
    {
        $webAppBtn = InlineKeyboard::webApp('Open App', 'https://app.example.com');
        $this->assertSame(['text' => 'Open App', 'web_app' => ['url' => 'https://app.example.com']], $webAppBtn);

        $switchQueryBtn = InlineKeyboard::switchInlineQuery('Share', 'query');
        $this->assertSame(['text' => 'Share', 'switch_inline_query' => 'query'], $switchQueryBtn);

        $switchCurrentChat = InlineKeyboard::switchInlineQueryCurrentChat('Search Here', 'query');
        $this->assertSame(['text' => 'Search Here', 'switch_inline_query_current_chat' => 'query'], $switchCurrentChat);
    }
}
