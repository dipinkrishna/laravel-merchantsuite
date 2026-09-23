<?php

namespace DK\MerchantSuite\Exceptions;

use DK\MerchantSuite\Enums\ErrorCode;
use DK\MerchantSuite\Support\Payload;

/**
 * The gateway answered with a non-2xx status.
 *
 * A declined card is NOT an ApiException: declines come back as 200 with a
 * non-zero responseCode on the Transaction.
 */
class ApiException extends MerchantSuiteException
{
    /**
     * @param  list<array{code: string|null, message: string|null, field: string|null}>  $details
     * @param  array<array-key, mixed>|null  $body
     */
    public function __construct(
        string $message,
        public readonly int $status,
        public readonly ErrorCode $errorCode = ErrorCode::Unspecified,
        public readonly array $details = [],
        public readonly ?array $body = null,
    ) {
        parent::__construct($message, $status);
    }

    /**
     * @param  array<array-key, mixed>|null  $body
     */
    public static function fromResponse(int $status, ?array $body): self
    {
        $outer = Payload::of($body);
        $error = Payload::of($outer->arr('error') ?? $body);

        $code = ErrorCode::tryFrom($error->str('code') ?? '') ?? ErrorCode::Unspecified;
        $message = $error->str('message') ?? "MerchantSuite returned HTTP {$status}.";

        $details = [];
        foreach ($error->list('details') as $detail) {
            $d = Payload::of($detail);
            $details[] = ['code' => $d->str('code'), 'message' => $d->str('message'), 'field' => $d->str('field')];
        }

        $class = match (true) {
            $status === 401, $status === 403,
            in_array($code, [ErrorCode::InvalidCredentials, ErrorCode::NotAuthenticated, ErrorCode::InvalidPermissions], true) => AuthenticationException::class,
            $code === ErrorCode::InvalidFields, $code === ErrorCode::InvalidPayload => ValidationException::class,
            $status === 404, $code === ErrorCode::NotFound => NotFoundException::class,
            default => self::class,
        };

        return new $class($message, $status, $code, $details, $body);
    }

    /**
     * Field-level messages keyed by field name, for showing next to a form.
     *
     * @return array<string, list<string>>
     */
    public function fieldErrors(): array
    {
        $errors = [];
        foreach ($this->details as $detail) {
            $errors[$detail['field'] ?? '_'][] = $detail['message'] ?? $detail['code'] ?? 'Invalid';
        }

        return $errors;
    }
}
