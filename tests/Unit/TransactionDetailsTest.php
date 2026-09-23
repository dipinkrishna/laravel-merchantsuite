<?php

use DK\MerchantSuite\Data\TransactionDetails;
use DK\MerchantSuite\Enums\Action;
use DK\MerchantSuite\Enums\TokenisationMode;

it('fills currency, biller code and test mode from defaults', function () {
    $details = new TransactionDetails(amount: 1999, crn1: 'ORDER-1');

    expect($details->toTwoPartyArray(['currency' => 'AUD', 'biller_code' => '1234567', 'test_mode' => false]))->toBe([
        'action' => 'Payment',
        'type' => 'Internet',
        'subType' => 'Single',
        'amount' => 1999,
        'currency' => 'AUD',
        'billerCode' => '1234567',
        'crn1' => 'ORDER-1',
        'testMode' => false,
    ]);
});

it('defaults to test mode when nothing says otherwise', function () {
    expect((new TransactionDetails(100, 'X'))->toTwoPartyArray()['testMode'])->toBeTrue();
});

it('lets a request override the configured test mode', function () {
    $details = new TransactionDetails(100, 'X', currency: 'NZD', testMode: false);

    expect($details->toTwoPartyArray(['currency' => 'AUD', 'test_mode' => true]))
        ->toMatchArray(['currency' => 'NZD', 'testMode' => false]);
});

it('only sends authkey-only fields on the authkey flow', function () {
    $details = new TransactionDetails(100, 'X', storeCard: true, tokenisationMode: TokenisationMode::OptIn);

    expect($details->toTwoPartyArray())->not->toHaveKeys(['storeCard', 'tokenisationMode'])
        ->and($details->toAuthkeyArray())->toMatchArray(['storeCard' => true, 'tokenisationMode' => 'OptIn']);
});

it('requires an original transaction for refunds, captures and reversals', function (Action $action) {
    new TransactionDetails(100, 'X', action: $action);
})->with([Action::Refund, Action::Capture, Action::Reversal])->throws(InvalidArgumentException::class);

it('rejects negative amounts and empty crn1', function () {
    expect(fn () => new TransactionDetails(-1, 'X'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => new TransactionDetails(1, ' '))->toThrow(InvalidArgumentException::class);
});
