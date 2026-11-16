<?php

namespace IVal\SiligramBot\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class OnCommand
{
    /**
     * Create a new OnCommand attribute instance.
     *
     * @param string|array<int, string> $commands One or more command names without slash (e.g. 'start' or ['start', 'restart'])
     */
    public function __construct(public string|array $commands)
    {
    }
}
