<?php

namespace DK\MerchantSuite\Data;

use Carbon\CarbonImmutable;
use DK\MerchantSuite\Enums\Action;
use DK\MerchantSuite\Support\Payload;

/**
 * A processed transaction as the gateway reports it.
 *
 * Declines are normal results, not exceptions: check isApproved().
 */
final readonly class Transaction
{
    /**
     * @param  array<string, mixed>  $raw  the full response, for fields not mapped here
     */
    public function __construct(
        public ?string $txnNumber,
        public ?string $receiptNumber,
        public ?Action $action,
        public ?string $responseCode,
        public ?string $responseText,
        public ?string $bankResponseCode,
        public int $amount,
        public ?int $amountSurcharge,
        public ?string $currency,
        public ?string $crn1,
        public ?string $crn2,
        public ?string $crn3,
        public ?string $merchantReference,
        public ?string $originalTxnNumber,
        public ?PaymentMethod $paymentMethod,
        public ?CarbonImmutable $processedAt,
        public ?string $settlementDate,
        public ?string $authoriseId,
        public bool $isTest,
        public bool $isThreeDs,
        public array $raw,
    ) {}

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $p = Payload::of($data);

        // AuthKey process wraps the transaction as {"txn": {...}}.
        if (($inner = $p->arr('txn')) !== null) {
            $p = Payload::of($inner);
        }

        $processed = $p->str('processedDateTime');

        return new self(
            txnNumber: $p->str('txnNumber'),
            receiptNumber: $p->str('receiptNumber'),
            action: Action::tryFrom($p->str('action') ?? ''),
            responseCode: $p->str('responseCode'),
            responseText: $p->str('responseText'),
            bankResponseCode: $p->str('bankResponseCode'),
            amount: $p->int('amount') ?? 0,
            amountSurcharge: $p->int('amountSurcharge'),
            currency: $p->str('currency'),
            crn1: $p->str('crn1'),
            crn2: $p->str('crn2'),
            crn3: $p->str('crn3'),
            merchantReference: $p->str('merchantReference'),
            originalTxnNumber: $p->str('originalTxnNumber'),
            paymentMethod: PaymentMethod::fromArray($p->arr('paymentMethod')),
            processedAt: $processed === null ? null : CarbonImmutable::parse($processed),
            settlementDate: $p->str('settlementDate'),
            authoriseId: $p->str('authoriseId'),
            isTest: $p->bool('isTestTxn') ?? false,
            isThreeDs: $p->bool('isThreeDs') ?? false,
            raw: $p->all(),
        );
    }

    /** responseCode "0" is the only approval. */
    public function isApproved(): bool
    {
        return $this->responseCode === '0';
    }

    /** China UnionPay results start as "P" and have to be polled. */
    public function isPending(): bool
    {
        return strtoupper((string) $this->responseCode) === 'P';
    }

    public function isDeclined(): bool
    {
        return ! $this->isApproved() && ! $this->isPending();
    }

    public function token(): ?string
    {
        return $this->paymentMethod?->token;
    }
}
