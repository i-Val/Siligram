<?php

namespace IVal\SiligramBot\Contracts;

use IVal\SiligramBot\DTOs\Update;

interface CommandHandler
{
    /**
     * Handle the incoming Telegram slash command.
     *
     * @param Update $update
     * @return mixed
     */
    public function handle(Update $update): mixed;
}
