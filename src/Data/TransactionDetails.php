<?php

namespace DK\MerchantSuite\Data;

use DK\MerchantSuite\Enums\Action;
use DK\MerchantSuite\Enums\SubType;
use DK\MerchantSuite\Enums\TokenisationMode;
use DK\MerchantSuite\Enums\TransactionType;
use InvalidArgumentException;

/**
 * What to charge, independent of how the card is supplied.
 *
 * currency, billerCode and testMode fall back to the package config when
 * left null.
 */
final readonly class TransactionDetails
{
    public function __construct(
        /** Amount in the currency's smallest unit, e.g. 1999 for $19.99. See Amount::fromDecimal(). */
        public int $amount,
        /** Customer reference number, your primary lookup key (order or invoice id). */
        public string $crn1,
        public Action $action = Action::Payment,
        public TransactionType $type = TransactionType::Internet,
        public SubType $subType = SubType::Single,
        public ?string $currency = null,
        public ?string $crn2 = null,
        public ?string $crn3 = null,
        /** Internal reference, not shown to the customer. */
        public ?string $merchantReference = null,
        public ?string $billerCode = null,
        public ?string $emailAddress = null,
        /** Required for Refund, Capture and Reversal. */
        public ?string $originalTxnNumber = null,
        public ?bool $testMode = null,
        /** AuthKey flow only. */
        public ?bool $storeCard = null,
        /** AuthKey flow only. */
        public ?TokenisationMode $tokenisationMode = null,
        /** AuthKey flow only. */
        public ?bool $bypass3ds = null,
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative.');
        }
        if (trim($crn1) === '') {
            throw new InvalidArgumentException('crn1 is required.');
        }
        if ($action->needsOriginalTransaction() && blank($originalTxnNumber)) {
            throw new InvalidArgumentException("{$action->value} needs originalTxnNumber.");
        }
    }

    /**
     * Body for POST /txns (2-party, card or token supplied in the same call).
     *
     * @param  array{currency?: string|null, biller_code?: string|null, test_mode?: bool}  $defaults
     * @return array<string, mixed>
     */
    public function toTwoPartyArray(array $defaults = []): array
    {
        return self::clean([
            ...$this->common($defaults),
            'originalTxnNumber' => $this->originalTxnNumber,
        ]);
    }

    /**
     * Body for PUT /txns/authkeys/{authkey}/txn-details.
     *
     * @param  array{currency?: string|null, biller_code?: string|null, test_mode?: bool}  $defaults
     * @return array<string, mixed>
     */
    public function toAuthkeyArray(array $defaults = []): array
    {
        return self::clean([
            ...$this->common($defaults),
            'storeCard' => $this->storeCard,
            'tokenisationMode' => $this->tokenisationMode?->value,
            'bypass3ds' => $this->bypass3ds,
        ]);
    }

    /**
     * @param  array{currency?: string|null, biller_code?: string|null, test_mode?: bool}  $defaults
     * @return array<string, mixed>
     */
    private function common(array $defaults): array
    {
        return [
            'action' => $this->action->value,
            'type' => $this->type->value,
            'subType' => $this->subType->value,
            'amount' => $this->amount,
            'currency' => $this->currency ?? $defaults['currency'] ?? null,
            'billerCode' => $this->billerCode ?? $defaults['biller_code'] ?? null,
            'crn1' => $this->crn1,
            'crn2' => $this->crn2,
            'crn3' => $this->crn3,
            'merchantReference' => $this->merchantReference,
            'emailAddress' => $this->emailAddress,
            'testMode' => $this->testMode ?? $defaults['test_mode'] ?? true,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function clean(array $data): array
    {
        return array_filter($data, fn ($v) => $v !== null && $v !== '');
    }
}
