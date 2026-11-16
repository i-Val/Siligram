<?php

namespace IVal\SiligramBot\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;

class InlineKeyboard implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * The array of button rows.
     *
     * @var array<int, array<int, array<string, mixed>>>
     */
    protected array $rows = [];

    /**
     * Create a new InlineKeyboard instance.
     */
    public static function make(): self
    {
        return new self();
    }

    /**
     * Add a row of buttons to the keyboard.
     *
     * @param array<int, array<string, mixed>> $buttons
     */
    public function row(array $buttons): self
    {
        $this->rows[] = $buttons;

        return $this;
    }

    /**
     * Add a row using variable arguments.
     *
     * @param array<string, mixed> ...$buttons
     */
    public function addRow(array ...$buttons): self
    {
        $this->rows[] = $buttons;

        return $this;
    }

    /**
     * Create a standard callback button.
     */
    public static function button(string $text, string $callbackData): array
    {
        return [
            'text' => $text,
            'callback_data' => $callbackData,
        ];
    }

    /**
     * Create an external URL button.
     */
    public static function url(string $text, string $url): array
    {
        return [
            'text' => $text,
            'url' => $url,
        ];
    }

    /**
     * Create a Web App button.
     */
    public static function webApp(string $text, string $url): array
    {
        return [
            'text' => $text,
            'web_app' => ['url' => $url],
        ];
    }

    /**
     * Create a switch inline query button.
     */
    public static function switchInlineQuery(string $text, string $query = ''): array
    {
        return [
            'text' => $text,
            'switch_inline_query' => $query,
        ];
    }

    /**
     * Create a switch inline query in current chat button.
     */
    public static function switchInlineQueryCurrentChat(string $text, string $query = ''): array
    {
        return [
            'text' => $text,
            'switch_inline_query_current_chat' => $query,
        ];
    }

    /**
     * Convert the keyboard to its array representation.
     *
     * @return array{inline_keyboard: array<int, array<int, array<string, mixed>>>}
     */
    public function toArray(): array
    {
        return [
            'inline_keyboard' => $this->rows,
        ];
    }

    /**
     * Convert the object to its JSON representation.
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Specify data which should be serialized to JSON.
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
