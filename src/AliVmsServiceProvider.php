<?php

namespace Listen\AliVms;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class AliVmsServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->publishes(
            [
                __DIR__ . '/../config/alivms.php' => config_path('alivms.php')
            ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/alivms.php', 'alivms'
        );

        // Bind captcha
        $this->app->singleton('alivms', function (Container $app) {
            return new AliVms(
                $app->make('Illuminate\Config\Repository')
            );
        });
    }
}
