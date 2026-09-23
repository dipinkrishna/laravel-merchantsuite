<?php

namespace DK\MerchantSuite\Tests;

use DK\MerchantSuite\MerchantSuiteServiceProvider;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    protected function getPackageProviders($app): array
    {
        return [MerchantSuiteServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('merchantsuite.username', 'api-user');
        $app['config']->set('merchantsuite.merchant_number', '5353109000000000');
        $app['config']->set('merchantsuite.password', 'secret');
        $app['config']->set('merchantsuite.biller_code', '1234567');
        $app['config']->set('merchantsuite.get_retries', 2);
    }
}
