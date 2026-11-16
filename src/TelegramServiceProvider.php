<?php

namespace IVal\SiligramBot;

use Illuminate\Support\ServiceProvider;
use IVal\SiligramBot\Commands\DeleteWebhookCommand;
use IVal\SiligramBot\Commands\SetWebhookCommand;
use IVal\SiligramBot\Commands\WebhookInfoCommand;
use IVal\SiligramBot\Support\CommandRegistry;

class TelegramServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/telegram.php', 'telegram');

        $this->app->singleton(CommandRegistry::class, function ($app) {
            return new CommandRegistry($app);
        });

        $this->app->singleton(TelegramClient::class, function ($app) {
            return new TelegramClient(
                token: config('telegram.token'),
                config: config('telegram', [])
            );
        });

        $this->app->alias(TelegramClient::class, 'telegram');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerRoutes();
        $this->publishResources();
        $this->bootCommandHandlers();
    }

    /**
     * Register package console commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SetWebhookCommand::class,
                DeleteWebhookCommand::class,
                WebhookInfoCommand::class,
            ]);
        }
    }

    /**
     * Register package routes.
     */
    protected function registerRoutes(): void
    {
        if (config('telegram.webhook.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/telegram.php');
        }
    }

    /**
     * Register publishable resources.
     */
    protected function publishResources(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/telegram.php' => config_path('telegram.php'),
            ], 'telegram-config');
        }
    }

    /**
     * Discover and bind configured command handlers.
     */
    protected function bootCommandHandlers(): void
    {
        $registry = $this->app->make(CommandRegistry::class);
        $commands = config('telegram.commands', []);

        foreach ($commands as $command => $handler) {
            if (is_int($command) && is_string($handler) && class_exists($handler)) {
                $registry->registerClass($handler);
            } elseif (is_string($command)) {
                $registry->register($command, $handler);
            }
        }
    }
}
