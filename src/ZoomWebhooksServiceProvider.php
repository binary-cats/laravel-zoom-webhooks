<?php

namespace BinaryCats\ZoomWebhooks;

use BinaryCats\ZoomWebhooks\ZoomWebhooksController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ZoomWebhooksServiceProvider extends ServiceProvider
{
    /**
     * Boot application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/zoom-webhooks.php' => config_path('zoom-webhooks.php'),
            ], 'config');
        }

        Route::macro('zoomWebhooks', function ($url) {
            return Route::post($url, ZoomWebhooksController::class);
        });
    }

    /**
     * Register application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/zoom-webhooks.php', 'zoom-webhooks');
    }
}
