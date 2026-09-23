<?php

namespace DK\MerchantSuite\Data;

use InvalidArgumentException;

/**
 * The customer references stored against a token.
 */
final readonly class TokenDetails
{
    public function __construct(
        /** Your customer id. Required. */
        public string $crn1,
        public ?string $crn2 = null,
        public ?string $crn3 = null,
        public ?string $emailAddress = null,
    ) {
        if (trim($crn1) === '') {
            throw new InvalidArgumentException('crn1 is required.');
        }
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'crn1' => $this->crn1,
            'crn2' => $this->crn2,
            'crn3' => $this->crn3,
            'emailAddress' => $this->emailAddress,
        ], fn ($v) => $v !== null && $v !== '');
    }
}
