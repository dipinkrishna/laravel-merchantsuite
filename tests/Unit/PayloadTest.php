<?php

use DK\MerchantSuite\Data\Transaction;
use DK\MerchantSuite\Support\Payload;

it('reads loosely typed json without type errors', function () {
    $p = Payload::of([
        'code' => 0,
        'text' => 'Approved',
        'empty' => '',
        'flag' => true,
        'amount' => '1999',
        'bad' => '19.99',
        'card' => ['number' => '512345...346'],
        'none' => [],
        'rows' => [['a' => 1], 'junk', ['b' => 2]],
        'expiry' => ['month' => 5, 'year' => '29', 'odd' => ['x']],
    ]);

    expect($p->str('code'))->toBe('0')
        ->and($p->str('empty'))->toBeNull()
        ->and($p->str('flag'))->toBeNull()
        ->and($p->str('missing'))->toBeNull()
        ->and($p->int('amount'))->toBe(1999)
        ->and($p->int('bad'))->toBeNull()
        ->and($p->bool('flag'))->toBeTrue()
        ->and($p->bool('text'))->toBeNull()
        ->and($p->arr('card'))->toBe(['number' => '512345...346'])
        ->and($p->arr('none'))->toBeNull()
        ->and($p->list('rows'))->toBe([['a' => 1], ['b' => 2]])
        ->and($p->list('text'))->toBe([])
        ->and($p->strings('expiry'))->toBe(['month' => '5', 'year' => '29']);
});

it('survives a response full of nulls', function () {
    $txn = Transaction::fromArray(array_fill_keys(['txnNumber', 'amount', 'paymentMethod', 'processedDateTime', 'isTestTxn'], null));

    expect($txn->txnNumber)->toBeNull()->and($txn->amount)->toBe(0)->and($txn->paymentMethod)->toBeNull()->and($txn->isApproved())->toBeFalse();
});

it('reads whole-number floats as integers', function () {
    $p = Payload::of(['a' => 1999.0, 'b' => 19.99, 'c' => '99999999999999999999']);

    expect($p->int('a'))->toBe(1999)->and($p->int('b'))->toBeNull()->and($p->int('c'))->toBeNull();
});

it('does not let one bad field sink the whole response', function () {
    $txn = Transaction::fromArray([
        'txnNumber' => '1',
        'responseCode' => '0',
        'processedDateTime' => 'not a date',
        'paymentMethod' => ['card' => ['number' => '512345...346', 'expiry' => ['month' => '13', 'year' => 'xx']]],
    ]);

    expect($txn->processedAt)->toBeNull()
        ->and($txn->paymentMethod->card->expiry)->toBeNull()
        ->and($txn->paymentMethod->card->number)->toBe('512345...346');
});
