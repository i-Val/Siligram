<?php

namespace IVal\SiligramBot\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use IVal\SiligramBot\DTOs\Update;
use IVal\SiligramBot\Events\TelegramUpdateReceived;
use IVal\SiligramBot\Support\CommandRegistry;

class WebhookController
{
    /**
     * Handle incoming Telegram webhook updates.
     */
    public function __invoke(Request $request, CommandRegistry $registry, ?string $bot = null): JsonResponse
    {
        $payload = $request->all();
        $update = Update::fromArray($payload);

        // 1. Dispatch event so listeners can intercept or log any update
        TelegramUpdateReceived::dispatch($update, $bot);

        // 2. Handle slash command if present
        if ($update->isCommand()) {
            $registry->execute($update);
        }

        return response()->json(['ok' => true]);
    }
}
