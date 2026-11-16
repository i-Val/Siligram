<?php

namespace IVal\SiligramBot\Tests;

if (class_exists(\Orchestra\Testbench\TestCase::class)) {
    abstract class BridgeTestCase extends \Orchestra\Testbench\TestCase {}
} elseif (class_exists(\Tests\TestCase::class)) {
    abstract class BridgeTestCase extends \Tests\TestCase {}
} else {
    abstract class BridgeTestCase extends \Illuminate\Foundation\Testing\TestCase {}
}

abstract class TestCase extends BridgeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'telegram.token' => '123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ',
            'telegram.default_chat_id' => '987654321',
            'telegram.webhook.secret' => 'secret-test-token',
            'telegram.webhook.path' => 'api/telegram/webhook',
            'telegram.webhook.enabled' => true,
            'telegram.bots' => [
                'default' => '123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ',
                'alerts' => '987654321:XYZ-alerts-token',
            ],
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            \IVal\SiligramBot\TelegramServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('telegram.token', '123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ');
        $app['config']->set('telegram.default_chat_id', '987654321');
        $app['config']->set('telegram.webhook.secret', 'secret-test-token');
        $app['config']->set('telegram.webhook.path', 'api/telegram/webhook');
        $app['config']->set('telegram.webhook.enabled', true);
        $app['config']->set('telegram.bots', [
            'default' => '123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ',
            'alerts' => '987654321:XYZ-alerts-token',
        ]);
    }
}
