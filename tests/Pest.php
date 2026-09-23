<?php

use DK\MerchantSuite\Tests\TestCase;

uses(TestCase::class)->in('Feature');

/**
 * Load a JSON fixture, optionally overriding top-level keys.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function ms_fixture(string $name, array $overrides = []): array
{
    $data = json_decode((string) file_get_contents(__DIR__."/Fixtures/{$name}.json"), true, flags: JSON_THROW_ON_ERROR);

    return array_replace($data, $overrides);
}
