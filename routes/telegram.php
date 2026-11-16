<?php

use Illuminate\Support\Facades\Route;
use IVal\SiligramBot\Http\Controllers\WebhookController;
use IVal\SiligramBot\Http\Middleware\VerifyTelegramSecret;

$path = config('telegram.webhook.path', 'api/telegram/webhook');
$middleware = config('telegram.webhook.middleware', ['api']);

// Primary default webhook endpoint
Route::post($path, WebhookController::class)
    ->middleware(array_merge($middleware, [VerifyTelegramSecret::class]))
    ->name('telegram.webhook');

// Optional named-bot endpoint (e.g. /api/telegram/webhook/alerts)
Route::post($path . '/{bot}', WebhookController::class)
    ->middleware(array_merge($middleware, [VerifyTelegramSecret::class]))
    ->name('telegram.webhook.bot');
