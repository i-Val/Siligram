<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Bot Token
    |--------------------------------------------------------------------------
    |
    | The default Telegram bot token supplied by BotFather.
    |
    */
    'token' => env('TELEGRAM_BOT_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Named Bots (Multi-Bot Support)
    |--------------------------------------------------------------------------
    |
    | Manage multiple Telegram bots within the same Laravel application.
    | You can switch between bots using Telegram::bot('alerts')->sendMessage(...).
    |
    */
    'bots' => [
        'default' => env('TELEGRAM_BOT_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Chat ID (Optional fallback for system alerts)
    |--------------------------------------------------------------------------
    */
    'default_chat_id' => env('TELEGRAM_DEFAULT_CHAT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the incoming webhook path and secret token for validation.
    | Telegram passes the secret token in X-Telegram-Bot-Api-Secret-Token.
    |
    */
    'webhook' => [
        'enabled' => env('TELEGRAM_WEBHOOK_ENABLED', true),
        'path' => env('TELEGRAM_WEBHOOK_PATH', 'api/telegram/webhook'),
        'secret' => env('TELEGRAM_WEBHOOK_SECRET'),
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Registered Command Handlers
    |--------------------------------------------------------------------------
    |
    | Map slash commands (e.g. 'start', 'help') to handler classes implementing
    | the IVal\SiligramBot\Contracts\CommandHandler interface.
    |
    */
    'commands' => [
        // 'start' => \App\Telegram\Commands\StartCommand::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Options
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('TELEGRAM_HTTP_TIMEOUT', 10),
    'parse_mode' => env('TELEGRAM_PARSE_MODE', 'HTML'),
];
