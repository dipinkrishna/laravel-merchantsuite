<?php

namespace DK\MerchantSuite\Data;

use SensitiveParameter;

/**
 * Australian bank account (BSB + account number) for direct-entry tokens.
 */
final class BankAccount
{
    public function __construct(
        public readonly string $bsb,
        #[SensitiveParameter] private readonly string $account,
        public readonly ?string $name = null,
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'bsb' => $this->bsb,
            'account' => $this->account,
            'name' => $this->name,
        ], fn ($v) => $v !== null);
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return ['bsb' => $this->bsb, 'account' => '...'.substr($this->account, -3), 'name' => $this->name];
    }

    public function __serialize(): array
    {
        throw new \LogicException('BankAccount cannot be serialised.');
    }
}
