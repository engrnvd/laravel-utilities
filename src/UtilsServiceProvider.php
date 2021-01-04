<?php

namespace Naveed\Utils;

use Illuminate\Support\ServiceProvider;

class UtilsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . "/views", "naveed.laravel-utilities");

        $this->publishes([
            __DIR__ . "/views" => base_path('resources/views/vendor/apm'),
            __DIR__ . "/config/apm-reporting.php" => config_path('apm-reporting.php'),
            __DIR__ . "/config/config.php" => config_path('apm-laravel-utilities.php'),
        ]);
    }

    public function register()
    {
    }
}
