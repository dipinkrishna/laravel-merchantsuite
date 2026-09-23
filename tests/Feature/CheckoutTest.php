<?php

use DK\MerchantSuite\Data\Expiry;
use DK\MerchantSuite\Data\TransactionDetails;
use DK\MerchantSuite\Enums\TokenisationMode;
use DK\MerchantSuite\Exceptions\MerchantSuiteException;
use DK\MerchantSuite\Facades\MerchantSuite;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

const CHECKOUT_BASE = 'https://www.merchantsuite.com/rest/v5/';

it('creates an authkey and attaches the transaction details', function () {
    Http::fake([
        CHECKOUT_BASE.'txns/authkeys' => Http::response(['authkey' => 'ak-123'], 201),
        CHECKOUT_BASE.'txns/authkeys/ak-123/txn-details' => Http::response(null, 201),
    ]);

    $authkey = MerchantSuite::checkout()->create(new TransactionDetails(
        amount: 4999,
        crn1: 'ORDER-9',
        emailAddress: 'jane@example.com',
        storeCard: true,
        tokenisationMode: TokenisationMode::OptIn,
    ));

    expect($authkey)->toBe('ak-123');
    Http::assertSentInOrder([
        fn (Request $r) => $r->method() === 'POST' && $r->url() === CHECKOUT_BASE.'txns/authkeys' && $r->body() === '{}',
        fn (Request $r) => $r->method() === 'PUT'
            && $r['amount'] === 4999
            && $r['tokenisationMode'] === 'OptIn'
            && $r['storeCard'] === true
            && $r['testMode'] === true,
    ]);
});

it('fails loudly if no authkey comes back', function () {
    Http::fake([CHECKOUT_BASE.'txns/authkeys' => Http::response([], 201)]);

    MerchantSuite::checkout()->create(new TransactionDetails(100, 'X'));
})->throws(MerchantSuiteException::class, 'did not return an authkey');

it('processes an authkey with a webhook and calculated surcharge', function () {
    Http::fake([CHECKOUT_BASE.'txns/authkeys/ak-123/process' => Http::response(['txn' => ms_fixture('txn-approved')])]);

    $txn = MerchantSuite::checkout()->process('ak-123', webhookUrl: 'https://shop.test/hooks/ms', surcharge: true);

    expect($txn->isApproved())->toBeTrue()->and($txn->txnNumber)->toBe('100000000123');
    Http::assertSent(fn (Request $r) => $r['webhook'] === ['url' => 'https://shop.test/hooks/ms'] && $r['surcharge'] === ['calculate' => true]);
});

it('reads back the payment method the browser attached', function () {
    Http::fake([CHECKOUT_BASE.'txns/authkeys/ak-123/payment-method' => Http::response([
        'accepted' => true,
        'surcharge' => 45,
        'card' => ms_fixture('txn-approved')['paymentMethod']['card'],
    ])]);

    $method = MerchantSuite::checkout()->paymentMethod('ak-123');

    expect($method->accepted)->toBeTrue()->and($method->surcharge)->toBe(45)->and($method->card->number)->toBe('512345...346');
});

it('charges a stored token in one call', function () {
    Http::fake([
        CHECKOUT_BASE.'txns/authkeys' => Http::response(['authkey' => 'ak-9'], 201),
        CHECKOUT_BASE.'txns/authkeys/ak-9/txn-details' => Http::response(null, 201),
        CHECKOUT_BASE.'txns/authkeys/ak-9/payment-method' => Http::response(['token' => '9999000011112222', 'accepted' => true]),
        CHECKOUT_BASE.'txns/authkeys/ak-9/process' => Http::response(['txn' => ms_fixture('txn-approved')]),
    ]);

    $txn = MerchantSuite::chargeToken('9999000011112222', new TransactionDetails(2500, 'INV-7'), expiry: new Expiry(8, 2031));

    expect($txn->isApproved())->toBeTrue();
    Http::assertSentCount(4);
    Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/payment-method')
        && $r['token'] === '9999000011112222'
        && $r['card'] === ['expiry' => ['month' => '08', 'year' => '31']]);
});

it('does not send an empty card when attaching a token', function () {
    Http::fake([CHECKOUT_BASE.'*' => Http::response(['token' => 't'])]);

    MerchantSuite::checkout()->attachToken('ak', 't');

    Http::assertSent(fn (Request $r) => $r->data() === ['token' => 't']);
});
