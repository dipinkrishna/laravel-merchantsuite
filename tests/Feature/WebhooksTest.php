<?php

use DK\MerchantSuite\Data\Token;
use DK\MerchantSuite\Data\Transaction;
use DK\MerchantSuite\Exceptions\InvalidWebhookException;
use DK\MerchantSuite\Facades\MerchantSuite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

function webhookRequest(array $body, string $ip = '203.195.127.4'): Request
{
    return Request::create('/hooks/merchantsuite', 'POST', server: ['REMOTE_ADDR' => $ip, 'CONTENT_TYPE' => 'application/json'], content: json_encode($body));
}

it('re-fetches the transaction instead of trusting the body', function () {
    Http::fake(['https://www.merchantsuite.com/rest/v5/txns/100000000123' => Http::response(ms_fixture('txn-declined', ['txnNumber' => '100000000123']))]);

    // A forged body claiming approval.
    $event = MerchantSuite::webhooks()->verify(webhookRequest(['type' => 'transaction', 'data' => ms_fixture('txn-approved')]));

    expect($event->verified)->toBeTrue()
        ->and($event->data)->toBeInstanceOf(Transaction::class)
        ->and($event->data->isApproved())->toBeFalse()
        ->and($event->idempotencyKey())->toBe('txn:100000000123');
});

it('re-fetches tokens', function () {
    Http::fake(['https://www.merchantsuite.com/rest/v5/tokens/9999000011112222' => Http::response(ms_fixture('token'))]);

    $event = MerchantSuite::webhooks()->verify(webhookRequest(['type' => 'token', 'data' => ms_fixture('token')]));

    expect($event->data)->toBeInstanceOf(Token::class)
        ->and($event->isToken())->toBeTrue()
        ->and($event->idempotencyKey())->toStartWith('token:9999000011112222:2026-09-23');
});

it('rejects a webhook for a transaction that does not exist', function () {
    Http::fake(['https://www.merchantsuite.com/rest/v5/txns/*' => Http::response(['code' => 'NotFound'], 404)]);

    MerchantSuite::webhooks()->verify(webhookRequest(['type' => 'transaction', 'data' => ['txnNumber' => 'made-up']]));
})->throws(InvalidWebhookException::class, 'does not have');

it('parses without calling the api', function () {
    $event = MerchantSuite::webhooks()->parse(webhookRequest(['type' => 'transaction', 'data' => ms_fixture('txn-approved')]));

    expect($event->verified)->toBeFalse()->and($event->isTransaction())->toBeTrue();
    Http::assertNothingSent();
});

it('rejects malformed payloads', function (array $body) {
    MerchantSuite::webhooks()->parse(webhookRequest($body));
})->with([
    'no type' => [['data' => []]],
    'unknown type' => [['type' => 'refund', 'data' => ['txnNumber' => '1']]],
    'no data' => [['type' => 'transaction']],
    'no txnNumber' => [['type' => 'transaction', 'data' => ['amount' => 1]]],
    'no token' => [['type' => 'token', 'data' => ['crn1' => 'x']]],
])->throws(InvalidWebhookException::class);

it('checks the source ip when enabled', function () {
    config(['merchantsuite.webhooks.verify_ip' => true]);
    app()->forgetInstance(DK\MerchantSuite\MerchantSuite::class);
    MerchantSuite::clearResolvedInstances();

    MerchantSuite::webhooks()->verify(webhookRequest(['type' => 'transaction', 'data' => ms_fixture('txn-approved')], ip: '198.51.100.7'));
})->throws(InvalidWebhookException::class, '198.51.100.7');
