<?php

namespace DK\MerchantSuite\Webhooks;

use DK\MerchantSuite\Data\Token;
use DK\MerchantSuite\Data\Transaction;

final readonly class WebhookEvent
{
    public function __construct(
        /** "transaction" or "token" */
        public string $type,
        public Transaction|Token $data,
        /** True when $data was re-fetched from the API rather than read from the request body. */
        public bool $verified,
    ) {}

    public function isTransaction(): bool
    {
        return $this->data instanceof Transaction;
    }

    public function isToken(): bool
    {
        return $this->data instanceof Token;
    }

    /**
     * The value MerchantSuite says to dedupe on: webhooks are retried
     * hourly for 24 hours and can arrive more than once.
     */
    public function idempotencyKey(): string
    {
        return $this->data instanceof Transaction
            ? 'txn:'.$this->data->txnNumber
            : 'token:'.$this->data->token.':'.$this->data->updatedAt?->toIso8601String();
    }
}
