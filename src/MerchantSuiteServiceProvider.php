<?php

namespace DK\MerchantSuite;

use DK\MerchantSuite\Http\Client;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\ServiceProvider;

class MerchantSuiteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/merchantsuite.php', 'merchantsuite');

        $this->app->singleton(MerchantSuite::class, function (Application $app) {
            /** @var array<string, mixed> $config */
            $config = (array) $app->make('config')->get('merchantsuite', []);

            return new MerchantSuite(new Client($app->make(Factory::class), $config), $config);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/merchantsuite.php' => config_path('merchantsuite.php'),
            ], 'merchantsuite-config');
        }
    }
}
