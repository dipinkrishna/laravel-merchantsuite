<?php

namespace DK\MerchantSuite\Support;

use WeakMap;

/**
 * Holds sensitive values (card numbers, CVNs, account numbers) outside the
 * object that owns them.
 *
 * A private property is still visible to Symfony's VarDumper (dd(), dump(),
 * Laravel's error page, Telescope), var_export() and an (array) cast.
 * Values kept here are not, and an entry goes away with its owner.
 *
 * @internal
 */
final class Secrets
{
    /** @var WeakMap<object, array<string, string|null>>|null */
    private static ?WeakMap $store = null;

    /**
     * @param  array<string, string|null>  $values
     */
    public static function put(object $owner, array $values): void
    {
        self::$store ??= new WeakMap;
        self::$store[$owner] = $values;
    }

    public static function get(object $owner, string $key): ?string
    {
        return self::$store[$owner][$key] ?? null;
    }
}
