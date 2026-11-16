<?php

namespace IVal\SiligramBot\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use IVal\SiligramBot\DTOs\Update;

class TelegramUpdateReceived
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Update $update
     * @param string|null $bot The bot name or identifier that received the update
     */
    public function __construct(
        public Update $update,
        public ?string $bot = null
    ) {
    }
}
