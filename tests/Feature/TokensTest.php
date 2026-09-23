<?php

use DK\MerchantSuite\Data\BankAccount;
use DK\MerchantSuite\Data\CardDetails;
use DK\MerchantSuite\Data\TokenDetails;
use DK\MerchantSuite\Exceptions\NotFoundException;
use DK\MerchantSuite\Exceptions\UnexpectedResponseException;
use DK\MerchantSuite\Facades\MerchantSuite;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

const TOKEN_BASE = 'https://www.merchantsuite.com/rest/v5/';

it('adds a card token without sending the cvn', function () {
    Http::fake([TOKEN_BASE.'tokens' => Http::response(ms_fixture('token'))]);

    $token = MerchantSuite::tokens()->add(
        new TokenDetails('CUST-42', emailAddress: 'jane@example.com'),
        new CardDetails('5123456789012346', '05/29', '123', 'Jane Citizen'),
    );

    expect($token->token)->toBe('9999000011112222')
        ->and($token->card->number)->toBe('512345...346')
        ->and($token->createdAt->year)->toBe(2026);

    Http::assertSent(fn (Request $r) => $r->data() === [
        'crn1' => 'CUST-42',
        'emailAddress' => 'jane@example.com',
        'card' => ['number' => '5123456789012346', 'expiry' => ['month' => '05', 'year' => '29'], 'name' => 'Jane Citizen'],
    ]);
});

it('adds a bank account token', function () {
    Http::fake([TOKEN_BASE.'tokens' => Http::response(ms_fixture('token'))]);

    MerchantSuite::tokens()->add(new TokenDetails('CUST-1'), new BankAccount('062000', '12345678', 'J Citizen'));

    Http::assertSent(fn (Request $r) => $r['bank'] === ['bsb' => '062000', 'account' => '12345678', 'name' => 'J Citizen'] && ! isset($r['card']));
});

it('finds, updates and deletes tokens', function () {
    Http::fake([
        TOKEN_BASE.'tokens/9999000011112222' => fn (Request $r) => $r->method() === 'DELETE'
            ? Http::response([])
            : Http::response(ms_fixture('token')),
    ]);

    $tokens = MerchantSuite::tokens();

    expect($tokens->find('9999000011112222')->crn1)->toBe('CUST-42');
    $tokens->update('9999000011112222', new TokenDetails('CUST-42'), new CardDetails('5123456789012346', '09/31'));
    $tokens->delete('9999000011112222');

    Http::assertSent(fn (Request $r) => $r->method() === 'PUT' && $r['card']['expiry'] === ['month' => '09', 'year' => '31']);
    Http::assertSent(fn (Request $r) => $r->method() === 'DELETE');
});

it('surfaces a missing token as NotFoundException', function () {
    Http::fake([TOKEN_BASE.'tokens/nope' => Http::response(['code' => 'NotFound', 'message' => 'Token not found'], 404)]);

    MerchantSuite::tokens()->find('nope');
})->throws(NotFoundException::class, 'Token not found');

it('tokenises the card from an earlier transaction', function () {
    Http::fake([TOKEN_BASE.'tokens/txn/100000000123' => Http::response(ms_fixture('token'))]);

    expect(MerchantSuite::tokens()->fromTransaction('100000000123')->token)->toBe('9999000011112222');
});

it('runs the token authkey flow', function () {
    Http::fake([
        TOKEN_BASE.'tokens/authkeys' => Http::response(['authkey' => 'tak-1'], 201),
        TOKEN_BASE.'tokens/authkeys/tak-1/token-details' => Http::response(null, 201),
        TOKEN_BASE.'tokens/authkeys/tak-1/process' => Http::response(['token' => ms_fixture('token')]),
    ]);

    $authkey = MerchantSuite::tokens()->createAuthkey(new TokenDetails('CUST-42'), existingToken: '9999000011112222');
    $token = MerchantSuite::tokens()->processAuthkey($authkey);

    expect($authkey)->toBe('tak-1')->and($token->token)->toBe('9999000011112222');
    Http::assertSent(fn (Request $r) => $r->url() === TOKEN_BASE.'tokens/authkeys' && $r['token'] === '9999000011112222');
    Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/token-details') && $r['crn1'] === 'CUST-42');
});

it('searches tokens', function () {
    Http::fake([TOKEN_BASE.'tokens/search' => Http::response(['resultCount' => 1, 'continueFrom' => null, 'tokens' => [ms_fixture('token')]])]);

    $page = MerchantSuite::tokens()->search(['expiredCardsOnly' => true]);

    expect($page)->toHaveCount(1)->and($page->hasMore())->toBeFalse()->and($page->items[0]->crn1)->toBe('CUST-42');
});

it('does not accept a token record without a token', function () {
    Http::fake([TOKEN_BASE.'tokens/x' => Http::response(['crn1' => 'CUST-42'])]);

    MerchantSuite::tokens()->find('x');
})->throws(UnexpectedResponseException::class);
