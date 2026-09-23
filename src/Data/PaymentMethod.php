<?php

namespace DK\MerchantSuite\Data;

use DK\MerchantSuite\Support\Payload;

final readonly class PaymentMethod
{
    /**
     * @param  array<string, string>|null  $bank  bsb, account (masked by the gateway) and name
     */
    public function __construct(
        public ?string $token,
        public ?MaskedCard $card,
        public ?array $bank,
        public ?string $provider,
        /** Only on AuthKey attach responses: whether biller rules accept this card. */
        public ?bool $accepted = null,
        /** Only on AuthKey attach responses: surcharge the biller rules add, in cents. */
        public ?int $surcharge = null,
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
        $provider = $p->str('provider');

        return new self(
            token: $p->str('token'),
            card: MaskedCard::fromArray($p->arr('card')),
            bank: $p->strings('bank'),
            provider: $provider === 'Unspecified' ? null : $provider,
            accepted: $p->bool('accepted'),
            surcharge: $p->int('surcharge'),
        );
    }
}
