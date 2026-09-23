<?php

use DK\MerchantSuite\Data\CardDetails;
use DK\MerchantSuite\Data\TransactionDetails;
use DK\MerchantSuite\Enums\CardScheme;
use DK\MerchantSuite\Exceptions\AuthenticationException;
use DK\MerchantSuite\Exceptions\ConfigurationException;
use DK\MerchantSuite\Exceptions\ConnectionException;
use DK\MerchantSuite\Exceptions\ValidationException;
use DK\MerchantSuite\Facades\MerchantSuite;
use Illuminate\Http\Client\ConnectionException as HttpConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

const BASE = 'https://www.merchantsuite.com/rest/v5/';

it('processes a 2-party payment', function () {
    Http::fake([BASE.'txns' => Http::response(ms_fixture('txn-approved'))]);

    $txn = MerchantSuite::transactions()->process(
        new TransactionDetails(amount: 1999, crn1: 'ORDER-1001', merchantReference: 'web checkout'),
        new CardDetails('5123456789012346', '05/29', '123', 'Jane Citizen'),
    );

    expect($txn->isApproved())->toBeTrue()
        ->and($txn->txnNumber)->toBe('100000000123')
        ->and($txn->amount)->toBe(1999)
        ->and($txn->paymentMethod->card->scheme)->toBe(CardScheme::Mastercard)
        ->and($txn->paymentMethod->card->lastDigits())->toBe('346')
        ->and($txn->processedAt->toDateString())->toBe('2026-09-23')
        ->and($txn->isTest)->toBeTrue();

    Http::assertSent(function (Request $request) {
        return $request->method() === 'POST'
            && $request->url() === BASE.'txns'
            && $request->header('Authorization')[0] === 'Basic '.base64_encode('api-user|5353109000000000:secret')
            && $request['amount'] === 1999
            && $request['currency'] === 'AUD'
            && $request['billerCode'] === '1234567'
            && $request['testMode'] === true
            && $request['cardDetails'] === [
                'number' => '5123456789012346',
                'expiry' => ['month' => '05', 'year' => '29'],
                'cvn' => '123',
                'name' => 'Jane Citizen',
            ];
    });
});

it('returns declines as results, not exceptions', function () {
    Http::fake([BASE.'txns' => Http::response(ms_fixture('txn-declined'))]);

    $txn = MerchantSuite::transactions()->process(new TransactionDetails(1005, 'ORDER-2'), new CardDetails('5123456789012346', '05/29'));

    expect($txn->isApproved())->toBeFalse()
        ->and($txn->isDeclined())->toBeTrue()
        ->and($txn->bankResponseCode)->toBe('05')
        ->and($txn->responseText)->toBe('Do not honour');
});

it('sends live transactions when test mode is off', function () {
    config(['merchantsuite.test_mode' => false]);
    Http::fake([BASE.'txns' => Http::response(ms_fixture('txn-approved'))]);

    MerchantSuite::transactions()->process(new TransactionDetails(100, 'X'), new CardDetails('5123456789012346', '05/29'));

    Http::assertSent(fn (Request $r) => $r['testMode'] === false);
})->after(fn () => app()->forgetInstance(DK\MerchantSuite\MerchantSuite::class));

it('refunds against the original transaction without card data', function () {
    Http::fake([BASE.'txns' => Http::response(ms_fixture('txn-approved', ['action' => 'Refund', 'originalTxnNumber' => '100000000123']))]);

    $txn = MerchantSuite::transactions()->refund('100000000123', 500, 'ORDER-1001');

    expect($txn->originalTxnNumber)->toBe('100000000123');
    Http::assertSent(fn (Request $r) => $r['action'] === 'Refund'
        && $r['originalTxnNumber'] === '100000000123'
        && $r['amount'] === 500
        && ! isset($r['cardDetails']));
});

it('captures and reverses pre-auths', function () {
    Http::fake([BASE.'txns' => Http::response(ms_fixture('txn-approved'))]);

    MerchantSuite::transactions()->capture('T1', 1999, 'ORDER-1');
    MerchantSuite::transactions()->reverse('T2', 1999, 'ORDER-1');

    Http::assertSent(fn (Request $r) => $r['action'] === 'Capture' && $r['originalTxnNumber'] === 'T1');
    Http::assertSent(fn (Request $r) => $r['action'] === 'Reversal' && $r['originalTxnNumber'] === 'T2');
});

it('looks up a transaction and an authkey result', function () {
    Http::fake([
        BASE.'txns/100000000123' => Http::response(ms_fixture('txn-approved')),
        BASE.'txns/resultkeys/*' => Http::response(ms_fixture('txn-approved')),
    ]);

    expect(MerchantSuite::transactions()->find('100000000123')->txnNumber)->toBe('100000000123')
        ->and(MerchantSuite::transactions()->result('rk/with slash')->isApproved())->toBeTrue();

    Http::assertSent(fn (Request $r) => $r->url() === BASE.'txns/resultkeys/rk%2Fwith%20slash');
});

it('turns field errors into a ValidationException', function () {
    Http::fake([BASE.'txns' => Http::response(ms_fixture('error-invalid-fields'), 400)]);

    try {
        MerchantSuite::transactions()->process(new TransactionDetails(0, 'X'), new CardDetails('5123456789012346', '05/29'));
    } catch (ValidationException $e) {
        expect($e->fieldErrors())->toBe([
            'crn1' => ['Crn1 is required'],
            'amount' => ['Amount must be greater than zero'],
        ])->and($e->getMessage())->not->toContain('5123456789012346');

        return;
    }

    $this->fail('Expected a ValidationException.');
});

it('throws on bad credentials', function () {
    Http::fake([BASE.'*' => Http::response('', 401)]);

    MerchantSuite::transactions()->find('1');
})->throws(AuthenticationException::class);

it('never retries a payment after a timeout', function () {
    $attempts = 0;
    Http::fake([BASE.'txns' => function () use (&$attempts) {
        $attempts++;

        throw new HttpConnectionException('cURL error 28: timed out');
    }]);

    try {
        MerchantSuite::transactions()->process(new TransactionDetails(100, 'X'), new CardDetails('5123456789012346', '05/29'));
    } catch (ConnectionException $e) {
        expect($e->outcomeUnknown())->toBeTrue()
            ->and($e->getMessage())->toContain('look the transaction up before retrying');
        expect($attempts)->toBe(1);

        return;
    }

    $this->fail('Expected a ConnectionException.');
});

it('retries lookups on connection errors', function () {
    $attempts = 0;
    Http::fake([BASE.'txns/1' => function () use (&$attempts) {
        if (++$attempts < 2) {
            throw new HttpConnectionException('reset');
        }

        return Http::response(ms_fixture('txn-approved'));
    }]);

    expect(MerchantSuite::transactions()->find('1')->isApproved())->toBeTrue()
        ->and($attempts)->toBe(2);
});

it('pages through search results lazily', function () {
    Http::fake([BASE.'txns/search' => Http::sequence()
        ->push(['resultCount' => 2, 'continueFrom' => 'page-2', 'transactions' => [ms_fixture('txn-approved'), ms_fixture('txn-declined')]])
        ->push(['resultCount' => 1, 'continueFrom' => null, 'transactions' => [ms_fixture('txn-approved', ['txnNumber' => '3'])]]),
    ]);

    $numbers = MerchantSuite::transactions()->cursor(['crn1' => 'ORDER-1001'], perPage: 2)->map->txnNumber->all();

    expect($numbers)->toBe(['100000000123', '100000000124', '3']);
    Http::assertSent(fn (Request $r) => $r['crn1'] === 'ORDER-1001' && $r['numberOfRecords'] === 2 && ! isset($r['continueFrom']));
    Http::assertSent(fn (Request $r) => ($r->data()['continueFrom'] ?? null) === 'page-2');
});

it('complains clearly when credentials are missing', function () {
    config(['merchantsuite.password' => null]);
    app()->forgetInstance(DK\MerchantSuite\MerchantSuite::class);
    MerchantSuite::clearResolvedInstances();

    MerchantSuite::transactions()->find('1');
})->throws(ConfigurationException::class, 'merchantsuite.password');
