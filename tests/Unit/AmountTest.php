<?php

use DK\MerchantSuite\Data\Amount;

it('converts decimal strings to cents without float rounding', function (string $in, int $out) {
    expect(Amount::fromDecimal($in))->toBe($out);
})->with([
    ['19.99', 1999],
    ['0.29', 29],
    ['1.1', 110],
    ['100', 10000],
    ['4.35', 435],   // (int) (4.35 * 100) === 434
    ['0.57', 57],    // (int) (0.57 * 100) === 56
]);

it('supports zero-decimal currencies', function () {
    expect(Amount::fromDecimal('500', 0))->toBe(500);
});

it('rejects bad amounts', function (string $in) {
    Amount::fromDecimal($in);
})->with(['-1', '1.234', 'abc', '1,000.00', ''])->throws(InvalidArgumentException::class);

it('formats cents back to a decimal string', function () {
    expect(Amount::toDecimal(1999))->toBe('19.99')
        ->and(Amount::toDecimal(5))->toBe('0.05')
        ->and(Amount::toDecimal(-250))->toBe('-2.50')
        ->and(Amount::toDecimal(500, 0))->toBe('500');
});
