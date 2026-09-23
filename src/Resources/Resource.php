<?php

namespace DK\MerchantSuite\Resources;

use DK\MerchantSuite\Exceptions\MerchantSuiteException;
use DK\MerchantSuite\Http\Client;

abstract class Resource
{
    /**
     * @param  array{currency?: string|null, biller_code?: string|null, test_mode?: bool}  $defaults
     */
    public function __construct(
        protected readonly Client $client,
        protected readonly array $defaults = [],
    ) {}

    /**
     * @param  array<array-key, mixed>  $response
     */
    protected static function authkeyFrom(array $response): string
    {
        $authkey = $response['authkey'] ?? null;

        if (! is_string($authkey) || $authkey === '') {
            throw new MerchantSuiteException('MerchantSuite did not return an authkey.');
        }

        return $authkey;
    }

    protected static function segment(string $value): string
    {
        return rawurlencode($value);
    }
}
