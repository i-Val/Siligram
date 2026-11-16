<?php

namespace IVal\SiligramBot\Support;

use Illuminate\Contracts\Container\Container;
use IVal\SiligramBot\Attributes\OnCommand;
use IVal\SiligramBot\Contracts\CommandHandler;
use IVal\SiligramBot\DTOs\Update;
use ReflectionClass;

class CommandRegistry
{
    /**
     * Map of command names to handlers.
     *
     * @var array<string, string|callable>
     */
    protected array $commands = [];

    public function __construct(protected Container $container)
    {
    }

    /**
     * Register a command handler.
     *
     * @param string $command Command name without slash
     * @param string|callable $handler Class name or callable
     */
    public function register(string $command, string|callable $handler): self
    {
        $this->commands[strtolower($command)] = $handler;

        return $this;
    }

    /**
     * Register multiple commands from an associative array.
     *
     * @param array<string, string|callable> $commands
     */
    public function registerMany(array $commands): self
    {
        foreach ($commands as $command => $handler) {
            $this->register($command, $handler);
        }

        return $this;
    }

    /**
     * Register a handler class discovering #[OnCommand] attributes.
     */
    public function registerClass(string $className): self
    {
        $reflection = new ReflectionClass($className);
        $attributes = $reflection->getAttributes(OnCommand::class);

        foreach ($attributes as $attribute) {
            /** @var OnCommand $instance */
            $instance = $attribute->newInstance();
            $commandList = is_array($instance->commands) ? $instance->commands : [$instance->commands];

            foreach ($commandList as $cmd) {
                $this->register($cmd, $className);
            }
        }

        return $this;
    }

    /**
     * Check if a command is registered.
     */
    public function has(string $command): bool
    {
        return isset($this->commands[strtolower($command)])
            || isset(config('telegram.commands', [])[strtolower($command)]);
    }

    /**
     * Get the registered handler for a command.
     */
    public function get(string $command): mixed
    {
        $key = strtolower($command);

        if (isset($this->commands[$key])) {
            return $this->commands[$key];
        }

        $configCommands = config('telegram.commands', []);

        return $configCommands[$key] ?? null;
    }

    /**
     * Execute the handler for an incoming update command.
     */
    public function execute(Update $update): mixed
    {
        $command = $update->command();
        if (!$command) {
            return null;
        }

        $handler = $this->get($command);
        if (!$handler) {
            return null;
        }

        if (is_string($handler)) {
            $resolved = $this->container->make($handler);

            if ($resolved instanceof CommandHandler) {
                return $resolved->handle($update);
            }

            if (is_callable($resolved)) {
                return $resolved($update);
            }
        } elseif (is_callable($handler)) {
            return $handler($update);
        }

        return null;
    }

    /**
     * Get all registered commands.
     *
     * @return array<string, string|callable>
     */
    public function all(): array
    {
        return array_merge(config('telegram.commands', []), $this->commands);
    }
}
