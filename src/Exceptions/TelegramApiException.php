<?php

namespace IVal\SiligramBot\Exceptions;

use Exception;

class TelegramApiException extends Exception
{
    /**
     * The raw response payload from Telegram.
     */
    protected array $responsePayload = [];

    /**
     * Create a new TelegramApiException instance.
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null, array $responsePayload = [])
    {
        parent::__construct($message, $code, $previous);
        $this->responsePayload = $responsePayload;
    }

    /**
     * Create an exception instance from a Telegram API response array.
     */
    public static function fromResponse(int $statusCode, array $body): self
    {
        $description = $body['description'] ?? 'Unknown Telegram API error.';
        $errorCode = $body['error_code'] ?? $statusCode;

        return new self("Telegram API Error [{$errorCode}]: {$description}", (int) $errorCode, null, $body);
    }

    /**
     * Get the raw response payload.
     */
    public function getResponsePayload(): array
    {
        return $this->responsePayload;
    }
}
