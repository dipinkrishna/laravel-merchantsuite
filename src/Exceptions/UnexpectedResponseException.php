<?php

namespace DK\MerchantSuite\Exceptions;

/**
 * The gateway answered 2xx, but not with what the endpoint returns: a body
 * that is not JSON (a proxy or maintenance page), or a transaction with no
 * response code.
 *
 * Treat it like a ConnectionException: for a payment, the outcome is
 * unknown until you look the transaction up.
 */
class UnexpectedResponseException extends MerchantSuiteException
{
    public function __construct(string $message, public readonly int $status)
    {
        parent::__construct($message, $status);
    }
}
