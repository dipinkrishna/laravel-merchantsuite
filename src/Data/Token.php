<?php

namespace DK\MerchantSuite\Data;

use Carbon\CarbonImmutable;
use DK\MerchantSuite\Support\Payload;

/**
 * A stored card or bank account. Save $token against your customer and
 * charge it later with MerchantSuite::chargeToken().
 */
final readonly class Token
{
    /**
     * @param  array<string, string>|null  $bank  bsb, account (masked) and name
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $token,
        public ?string $crn1,
        public ?string $crn2,
        public ?string $crn3,
        public ?string $emailAddress,
        public ?MaskedCard $card,
        public ?array $bank,
        public ?CarbonImmutable $createdAt,
        public ?CarbonImmutable $updatedAt,
        public array $raw,
    ) {}

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $p = Payload::of($data);

        // AuthKey process wraps the token as {"token": {...}}.
        if (($inner = $p->arr('token')) !== null) {
            $p = Payload::of($inner);
        }

        $method = Payload::of($p->arr('paymentMethod'));

        return new self(
            token: $p->str('token') ?? '',
            crn1: $p->str('crn1'),
            crn2: $p->str('crn2'),
            crn3: $p->str('crn3'),
            emailAddress: $p->str('emailAddress'),
            card: MaskedCard::fromArray($method->arr('card')),
            bank: $method->strings('bank'),
            createdAt: $p->date('createdDateTime'),
            updatedAt: $p->date('updatedDateTime'),
            raw: $p->all(),
        );
    }
}
