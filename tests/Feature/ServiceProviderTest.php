<?php

use DK\MerchantSuite\MerchantSuite;

it('registers a singleton', function () {
    expect(app(MerchantSuite::class))->toBe(app(MerchantSuite::class));
});

it('ships safe config defaults', function () {
    $config = require __DIR__.'/../../config/merchantsuite.php';

    expect($config['test_mode'])->toBeTrue()
        ->and($config['base_url'])->toBe('https://www.merchantsuite.com/rest/v5')
        ->and($config['timeout'])->toBeGreaterThan(50)
        ->and($config['webhooks']['verify_ip'])->toBeFalse();
});
