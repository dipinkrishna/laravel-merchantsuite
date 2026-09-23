<?php

use DK\MerchantSuite\Enums\ErrorCode;
use DK\MerchantSuite\Exceptions\ApiException;
use DK\MerchantSuite\Exceptions\AuthenticationException;
use DK\MerchantSuite\Exceptions\NotFoundException;
use DK\MerchantSuite\Exceptions\ValidationException;

it('maps error responses to exception types', function (int $status, ?array $body, string $class) {
    expect(ApiException::fromResponse($status, $body))->toBeInstanceOf($class);
})->with([
    [401, null, AuthenticationException::class],
    [400, ['code' => 'InvalidCredentials'], AuthenticationException::class],
    [400, ['code' => 'InvalidPermissions'], AuthenticationException::class],
    [400, ['code' => 'InvalidFields'], ValidationException::class],
    [404, null, NotFoundException::class],
    [400, ['code' => 'NotFound'], NotFoundException::class],
    [500, ['code' => 'SystemDown'], ApiException::class],
    [502, null, ApiException::class],
]);

it('keeps the gateway message, code and field details', function () {
    $e = ApiException::fromResponse(400, [
        'code' => 'InvalidFields',
        'message' => 'One or more fields are invalid',
        'details' => [
            ['code' => 'Mandatory', 'message' => 'Crn1 is required', 'field' => 'crn1'],
            ['code' => 'MaxLength', 'message' => 'Too long', 'field' => 'crn1'],
        ],
    ]);

    expect($e->getMessage())->toBe('One or more fields are invalid')
        ->and($e->errorCode)->toBe(ErrorCode::InvalidFields)
        ->and($e->status)->toBe(400)
        ->and($e->fieldErrors())->toBe(['crn1' => ['Crn1 is required', 'Too long']]);
});

it('reads an error nested under an error key', function () {
    $e = ApiException::fromResponse(400, ['error' => ['code' => 'InvalidFlow', 'message' => 'Authkey already processed']]);

    expect($e->errorCode)->toBe(ErrorCode::InvalidFlow)->and($e->getMessage())->toBe('Authkey already processed');
});

it('falls back to a generic message for unknown bodies', function () {
    expect(ApiException::fromResponse(503, null)->getMessage())->toBe('MerchantSuite returned HTTP 503.');
});
