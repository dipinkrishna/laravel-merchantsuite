<?php

namespace DK\MerchantSuite\Data;

use DK\MerchantSuite\Enums\CardScheme;
use DK\MerchantSuite\Support\Payload;

final readonly class MaskedCard
{
    public function __construct(
        /** e.g. "512345...346" */
        public ?string $number,
        public ?Expiry $expiry,
        public ?string $name,
        public ?CardScheme $scheme,
        /** Local or International */
        public ?string $localisation,
        /** Debit, Credit or ChargeCard */
        public ?string $type,
    ) {}

    /**
     * @param  array<array-key, mixed>|null  $data
     */
    public static function fromArray(?array $data): ?self
    {
        if (! $data) {
            return null;
        }

        $p = Payload::of($data);

        return new self(
            number: $p->str('number'),
            expiry: Expiry::fromArray($p->strings('expiry')),
            name: $p->str('name'),
            scheme: CardScheme::tryFrom($p->str('scheme') ?? ''),
            localisation: $p->str('localisation'),
            type: $p->str('type'),
        );
    }

    public function lastDigits(): ?string
    {
        return $this->number === null ? null : substr($this->number, -3);
    }
}
