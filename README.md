# Siligram Bot — Laravel Telegram Bot API Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/i-val/siligram-bot.svg?style=flat-square)](https://packagist.org/packages/i-val/siligram-bot)
[![Total Downloads](https://img.shields.io/packagist/dt/i-val/siligram-bot.svg?style=flat-square)](https://packagist.org/packages/i-val/siligram-bot)
[![License](https://img.shields.io/packagist/l/i-val/siligram-bot.svg?style=flat-square)](https://packagist.org/packages/i-val/siligram-bot)

A lightweight, developer-friendly Telegram Bot API client and incoming webhook handler for **Laravel 11.x & 12.x** (PHP 8.2+).

Zero third-party HTTP bloat—built entirely on Laravel's native `Illuminate\Support\Facades\Http`, Event Dispatcher, and Service Container.

---

## Features

- **Fluent Telegram Client & Facade:** Simple, expressive API for `sendMessage`, `sendPhoto`, `sendDocument`, `editMessageText`, `answerCallbackQuery`, and custom API endpoints.
- **Multi-Bot Support:** Seamlessly switch between configured bot tokens on the fly (`Telegram::bot('alerts')->sendMessage(...)`).
- **Timing-Safe Webhook Security:** Automatic verification of incoming `X-Telegram-Bot-Api-Secret-Token` via `hash_equals()`.
- **Typed `Update` DTO:** Normalizes messages, slash commands, callback queries, chat IDs, sender details, and arguments into a clean object.
- **Slash Command Routing:** Class-based command handlers and PHP 8 `#[OnCommand]` attribute discovery.
- **Fluent Inline Keyboard Builder:** Construct interactive button grids and URL buttons effortlessly without manual array nesting.
- **Built-in Artisan CLI Tooling:** Register, remove, and inspect webhook endpoints directly from your command line or deployment scripts.

---

## Installation

Install the package via Composer:

```bash
composer require i-val/siligram-bot
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="telegram-config"
```

This creates `config/telegram.php`.

---

## Configuration

Add your bot credentials and webhook secret to `.env`:

```env
TELEGRAM_BOT_TOKEN="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ"
TELEGRAM_DEFAULT_CHAT_ID="987654321"
TELEGRAM_WEBHOOK_SECRET="your-high-entropy-secret-token"
TELEGRAM_WEBHOOK_PATH="api/telegram/webhook"
```

### Multi-Bot Configuration (`config/telegram.php`)

```php
return [
    'token' => env('TELEGRAM_BOT_TOKEN'),

    'bots' => [
        'default' => env('TELEGRAM_BOT_TOKEN'),
        'alerts'  => env('TELEGRAM_ALERTS_BOT_TOKEN'),
    ],

    'default_chat_id' => env('TELEGRAM_DEFAULT_CHAT_ID'),

    'webhook' => [
        'enabled' => env('TELEGRAM_WEBHOOK_ENABLED', true),
        'path' => env('TELEGRAM_WEBHOOK_PATH', 'api/telegram/webhook'),
        'secret' => env('TELEGRAM_WEBHOOK_SECRET'),
        'middleware' => ['api'],
    ],

    'commands' => [
        'start' => \App\Telegram\Commands\StartCommand::class,
    ],

    'timeout' => 10,
    'parse_mode' => 'HTML',
];
```

---

## Usage

### 1. Sending Messages

```php
use IVal\SiligramBot\Facades\Telegram;

// Send a simple message (defaults to HTML parse mode)
Telegram::sendMessage($chatId, '<b>Hello from Laravel!</b>');

// Send with markdown or custom options
Telegram::sendMessage($chatId, '*Bold text*', [
    'parse_mode' => 'MarkdownV2',
    'disable_web_page_preview' => true,
]);
```

### 2. Multi-Bot Switching

```php
// Dispatch via a secondary bot defined in config/telegram.php
Telegram::bot('alerts')->sendMessage($chatId, '⚠️ High CPU usage detected!');

// Or pass an explicit bot token
Telegram::bot('987654321:CUSTOM_TOKEN')->sendMessage($chatId, 'Direct message');
```

### 3. Media & File Uploads

Supports remote URLs, Telegram `file_id`, or local file paths (uploaded automatically via multipart form):

```php
// Send photo via URL or file_id
Telegram::sendPhoto($chatId, 'https://example.com/banner.png', 'Check this out!');

// Send photo from local disk
Telegram::sendPhoto($chatId, storage_path('app/charts/monthly.png'), 'Monthly Report');

// Send document
Telegram::sendDocument($chatId, storage_path('app/invoices/inv-101.pdf'), 'Your Invoice');
```

### 4. Interactive Inline Keyboards

```php
use IVal\SiligramBot\Facades\Telegram;
use IVal\SiligramBot\Support\InlineKeyboard;

$keyboard = InlineKeyboard::make()
    ->row([
        InlineKeyboard::button('✅ Approve', 'order:42:approve'),
        InlineKeyboard::button('❌ Reject', 'order:42:reject'),
    ])
    ->row([
        InlineKeyboard::url('🔗 View Order', 'https://example.com/orders/42'),
    ]);

Telegram::sendMessage($chatId, 'New order received:', [
    'reply_markup' => $keyboard,
]);
```

### 5. Answering Callback Queries & Updating Messages

When a user taps an inline button, dismiss the loading spinner and edit the message:

```php
// In your webhook handler or event listener:
Telegram::answerCallbackQuery($update->callbackQueryId(), 'Action completed!');

Telegram::editMessageText($update->chatId(), $update->messageId(), 'Order #42 was approved.');
```

---

## Webhooks & Command Routing

### Incoming Webhook Architecture

The package automatically registers POST routes:
- `/api/telegram/webhook` (Default bot)
- `/api/telegram/webhook/{bot}` (Named bots)

Incoming requests are verified against `X-Telegram-Bot-Api-Secret-Token` via `VerifyTelegramSecret` middleware.

### Listening to All Updates

Listen to `TelegramUpdateReceived` event in `EventServiceProvider` or `bootstrap/app.php`:

```php
use IVal\SiligramBot\Events\TelegramUpdateReceived;

Event::listen(TelegramUpdateReceived::class, function (TelegramUpdateReceived $event) {
    $update = $event->update;

    Log::info("Telegram Update #{$update->updateId()} received from {$update->username()}");
});
```

### Creating Command Handlers

Create a class implementing `CommandHandler`:

```php
namespace App\Telegram\Commands;

use IVal\SiligramBot\Contracts\CommandHandler;
use IVal\SiligramBot\DTOs\Update;
use IVal\SiligramBot\Facades\Telegram;

class StartCommand implements CommandHandler
{
    public function handle(Update $update): mixed
    {
        $chatId = $update->chatId();
        $name = $update->firstName() ?? 'friend';

        Telegram::sendMessage($chatId, "Hello, {$name}! Welcome to our bot.");

        return true;
    }
}
```

#### Registering Commands

**Option A: Via `config/telegram.php`**
```php
'commands' => [
    'start' => \App\Telegram\Commands\StartCommand::class,
    'help'  => \App\Telegram\Commands\HelpCommand::class,
],
```

**Option B: Using `#[OnCommand]` PHP 8 Attribute**
```php
namespace App\Telegram\Commands;

use IVal\SiligramBot\Attributes\OnCommand;
use IVal\SiligramBot\Contracts\CommandHandler;
use IVal\SiligramBot\DTOs\Update;

#[OnCommand(['status', 'health'])]
class StatusCommand implements CommandHandler
{
    public function handle(Update $update): mixed
    {
        // Handles both /status and /health
    }
}
```

Then register the class in `config/telegram.php`:
```php
'commands' => [
    \App\Telegram\Commands\StatusCommand::class,
],
```

---

## Artisan Commands

### Set Webhook
```bash
# Sets webhook using config URL and secret token
php artisan telegram:webhook:set

# Set for specific bot or custom URL
php artisan telegram:webhook:set --bot=alerts --url=https://your-domain.com/api/telegram/webhook/alerts
```

### Webhook Status
```bash
php artisan telegram:webhook:info
```

### Delete Webhook
```bash
php artisan telegram:webhook:delete --drop-pending-updates
```

---

## Testing

Run the package test suite:

```bash
composer test
```

Using `Http::fake()` in your Laravel application tests:

```php
use Illuminate\Support\Facades\Http;
use IVal\SiligramBot\Facades\Telegram;

public function test_telegram_notification()
{
    Http::fake([
        'https://api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]], 200),
    ]);

    Telegram::sendMessage(123456, 'Test Alert');

    Http::assertSent(fn ($req) => $req['text'] === 'Test Alert');
}
```

---

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
