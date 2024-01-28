<?php

namespace BinaryCats\ZoomWebhooks\Tests;

use BinaryCats\ZoomWebhooks\ZoomWebhooksServiceProvider;
use Throwable;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    /** @return void */
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * Set up the environment.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        config(['zoom-webhooks.signing_secret' => 'test_signing_secret']);
    }

    protected function setUpDatabase()
    {
        $migration = include __DIR__.'/../vendor/spatie/laravel-webhook-client/database/migrations/create_webhook_calls_table.php.stub';

        $migration->up();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ZoomWebhooksServiceProvider::class,
        ];
    }

    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct()
            {
            }

            public function report(Throwable $e)
            {
            }

            public function render($request, Throwable $exception)
            {
                throw $exception;
            }
        });
    }

    protected function determineZoomSignature(string $configKey = null): string
    {
        return ($configKey) ?
            config("zoom-webhooks.signing_secret_{$configKey}") :
            config('zoom-webhooks.signing_secret');
    }
}
