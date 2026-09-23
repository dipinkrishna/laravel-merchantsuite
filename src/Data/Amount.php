<?php

namespace DK\MerchantSuite\Data;

use InvalidArgumentException;

/**
 * The API takes amounts as integers in the currency's smallest unit (cents
 * for AUD). This converts a decimal string without going through a float,
 * so "19.99" is always 1999 and never 1998.
 */
final class Amount
{
    public static function fromDecimal(string|int $amount, int $decimals = 2): int
    {
        $amount = trim((string) $amount);

        if (! preg_match('/^(\d+)(?:\.(\d+))?$/', $amount, $m)) {
            throw new InvalidArgumentException("\"{$amount}\" is not a positive decimal amount.");
        }

        $fraction = $m[2] ?? '';
        if (strlen($fraction) > $decimals) {
            throw new InvalidArgumentException("\"{$amount}\" has more than {$decimals} decimal places.");
        }

        return (int) ($m[1].str_pad($fraction, $decimals, '0'));
    }

    public static function toDecimal(int $minor, int $decimals = 2): string
    {
        if ($decimals === 0) {
            return (string) $minor;
        }

        $sign = $minor < 0 ? '-' : '';
        $digits = str_pad((string) abs($minor), $decimals + 1, '0', STR_PAD_LEFT);

        return $sign.substr($digits, 0, -$decimals).'.'.substr($digits, -$decimals);
    }
}
