<?php

namespace IVal\SiligramBot\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTelegramSecret
{
    /**
     * Handle an incoming request and verify the Telegram secret header.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @param string|null $secret Custom secret or config override
     * @return Response
     */
    public function handle(Request $request, Closure $next, ?string $secret = null): Response
    {
        $expectedSecret = $secret ?? config('telegram.webhook.secret');

        // If a secret token is configured, enforce strict timing-safe comparison
        if (!empty($expectedSecret)) {
            $receivedToken = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');

            if (empty($receivedToken) || !hash_equals($expectedSecret, $receivedToken)) {
                abort(403, 'Invalid Telegram secret token.');
            }
        }

        return $next($request);
    }
}
