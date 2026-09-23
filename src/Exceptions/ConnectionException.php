<?php

namespace DK\MerchantSuite\Exceptions;

use Throwable;

/**
 * The request never got a response: DNS, TLS, connect or read timeout.
 *
 * For a POST that means the outcome is unknown. The gateway may have
 * processed the payment. Look it up (search by crn1 or merchantReference)
 * before trying again, or you risk charging twice.
 */
class ConnectionException extends MerchantSuiteException
{
    public function __construct(
        string $message,
        public readonly string $method,
        public readonly string $path,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function outcomeUnknown(): bool
    {
        return $this->method !== 'GET';
    }
}
