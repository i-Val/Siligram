<?php

namespace IVal\SiligramBot\DTOs;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

class Update implements Arrayable, Jsonable, JsonSerializable, ArrayAccess
{
    /**
     * Create a new Update instance.
     *
     * @param array<string, mixed> $payload
     */
    public function __construct(protected array $payload)
    {
    }

    /**
     * Factory to instantiate from an array.
     */
    public static function fromArray(array $payload): self
    {
        return new self($payload);
    }

    /**
     * Unique Telegram update identifier.
     */
    public function updateId(): int
    {
        return (int) ($this->payload['update_id'] ?? 0);
    }

    /**
     * Get the primary message payload (standard, edited, channel_post, or callback query message).
     */
    public function message(): ?array
    {
        return $this->payload['message']
            ?? $this->payload['edited_message']
            ?? $this->payload['channel_post']
            ?? $this->payload['edited_channel_post']
            ?? $this->payload['callback_query']['message']
            ?? null;
    }

    /**
     * Get the message ID if available.
     */
    public function messageId(): ?int
    {
        $message = $this->message();

        return isset($message['message_id']) ? (int) $message['message_id'] : null;
    }

    /**
     * Extracts the chat ID whether the update is a message, edited message, or callback query.
     */
    public function chatId(): ?int
    {
        if (isset($this->payload['callback_query']['message']['chat']['id'])) {
            return (int) $this->payload['callback_query']['message']['chat']['id'];
        }

        $message = $this->message();
        if (isset($message['chat']['id'])) {
            return (int) $message['chat']['id'];
        }

        if (isset($this->payload['callback_query']['from']['id'])) {
            return (int) $this->payload['callback_query']['from']['id'];
        }

        return null;
    }

    /**
     * Extracts message text or caption.
     */
    public function text(): ?string
    {
        $message = $this->message();

        return $message['text'] ?? $message['caption'] ?? null;
    }

    /**
     * Check if the incoming text is a slash command.
     */
    public function isCommand(): bool
    {
        $text = $this->text();

        return $text !== null && str_starts_with(trim($text), '/');
    }

    /**
     * Extracts the normalized command name (e.g. '/start@MyBot arg1' -> 'start').
     */
    public function command(): ?string
    {
        if (!$this->isCommand()) {
            return null;
        }

        $text = trim((string) $this->text());
        $parts = explode(' ', $text, 2);
        $commandWithPrefix = $parts[0];

        // Strip leading slash
        $commandName = ltrim($commandWithPrefix, '/');

        // Strip bot username mention if present (/command@BotName)
        if (str_contains($commandName, '@')) {
            $commandName = explode('@', $commandName, 2)[0];
        }

        return strtolower($commandName);
    }

    /**
     * Returns the trailing arguments after the slash command.
     */
    public function commandArguments(): string
    {
        if (!$this->isCommand()) {
            return '';
        }

        $text = trim((string) $this->text());
        $parts = explode(' ', $text, 2);

        return $parts[1] ?? '';
    }

    /**
     * Determines if the update originated from an inline button callback query.
     */
    public function isCallbackQuery(): bool
    {
        return isset($this->payload['callback_query']);
    }

    /**
     * Extracts the callback_query array.
     */
    public function callbackQuery(): ?array
    {
        return $this->payload['callback_query'] ?? null;
    }

    /**
     * Extracts the callback query ID.
     */
    public function callbackQueryId(): ?string
    {
        return $this->payload['callback_query']['id'] ?? null;
    }

    /**
     * Extracts the callback_data string.
     */
    public function callbackData(): ?string
    {
        return $this->payload['callback_query']['data'] ?? null;
    }

    /**
     * Extracts the user details who sent the message or triggered the action.
     */
    public function from(): ?array
    {
        if (isset($this->payload['callback_query']['from'])) {
            return $this->payload['callback_query']['from'];
        }

        $message = $this->message();

        return $message['from'] ?? null;
    }

    /**
     * Extracts the sender user ID.
     */
    public function fromId(): ?int
    {
        $from = $this->from();

        return isset($from['id']) ? (int) $from['id'] : null;
    }

    /**
     * Extracts the sender username without '@'.
     */
    public function username(): ?string
    {
        $from = $this->from();

        return $from['username'] ?? null;
    }

    /**
     * Get the sender's first name.
     */
    public function firstName(): ?string
    {
        $from = $this->from();

        return $from['first_name'] ?? null;
    }

    /**
     * Get the sender's last name.
     */
    public function lastName(): ?string
    {
        $from = $this->from();

        return $from['last_name'] ?? null;
    }

    /**
     * Returns the raw Telegram payload array.
     */
    public function raw(): array
    {
        return $this->payload;
    }

    /**
     * Arrayable interface.
     */
    public function toArray(): array
    {
        return $this->payload;
    }

    /**
     * Jsonable interface.
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->payload, $options);
    }

    /**
     * JsonSerializable interface.
     */
    public function jsonSerialize(): mixed
    {
        return $this->payload;
    }

    /**
     * ArrayAccess: offsetExists
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->payload[$offset]);
    }

    /**
     * ArrayAccess: offsetGet
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->payload[$offset] ?? null;
    }

    /**
     * ArrayAccess: offsetSet
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->payload[$offset] = $value;
    }

    /**
     * ArrayAccess: offsetUnset
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->payload[$offset]);
    }
}
