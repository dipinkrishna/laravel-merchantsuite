<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API credentials
    |--------------------------------------------------------------------------
    |
    | Created in the MerchantSuite back office under Settings > User Management
    | with the "API" permission. Requests authenticate with HTTP basic auth as
    | "username|merchant_number" and the password.
    |
    */

    'username' => env('MERCHANTSUITE_USERNAME'),

    'merchant_number' => env('MERCHANTSUITE_MERCHANT_NUMBER'),

    'password' => env('MERCHANTSUITE_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Test mode
    |--------------------------------------------------------------------------
    |
    | When true, every transaction is sent with testMode=true and never reaches
    | the bank. It defaults to true so a missing env var cannot charge a real
    | card. Set MERCHANTSUITE_TEST_MODE=false in production. A request can
    | still override it per transaction.
    |
    */

    'test_mode' => (bool) env('MERCHANTSUITE_TEST_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */

    'biller_code' => env('MERCHANTSUITE_BILLER_CODE'),

    'currency' => env('MERCHANTSUITE_CURRENCY', 'AUD'),

    /*
    |--------------------------------------------------------------------------
    | HTTP
    |--------------------------------------------------------------------------
    |
    | The gateway can take up to ~50 seconds to answer a slow bank, so keep the
    | timeout above that. Only GET requests are retried (on connection errors
    | and 502/503/504), get_retries times after the first attempt: a POST that
    | timed out may still have charged the card.
    |
    */

    'base_url' => env('MERCHANTSUITE_BASE_URL', 'https://www.merchantsuite.com/rest/v5'),

    'timeout' => (int) env('MERCHANTSUITE_TIMEOUT', 65),

    'connect_timeout' => (int) env('MERCHANTSUITE_CONNECT_TIMEOUT', 10),

    'get_retries' => 2,

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    | MerchantSuite webhooks are not signed. The package treats the payload as
    | a hint and re-fetches the transaction or token from the API before
    | returning it. The source IP check is an extra layer, off by default
    | because it needs the app to see real client IPs (TrustProxies).
    |
    */

    'webhooks' => [
        'verify_ip' => (bool) env('MERCHANTSUITE_WEBHOOK_VERIFY_IP', false),

        'allowed_ips' => [
            '203.195.127.4', // production
            '202.166.187.3', // production
            '103.91.167.3',  // UAT
        ],
    ],

];
