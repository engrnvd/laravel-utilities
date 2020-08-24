<?php

namespace Naveed\Utils;

use Illuminate\Support\ServiceProvider;

class UtilsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . "/config/config.php" => config_path('apm-laravel-utilities.php'),
        ]);
    }

    public function register()
    {
    }
}
