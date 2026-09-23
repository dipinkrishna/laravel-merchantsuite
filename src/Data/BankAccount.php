<?php

namespace DK\MerchantSuite\Data;

use DK\MerchantSuite\Support\Secrets;
use InvalidArgumentException;
use LogicException;
use SensitiveParameter;

/**
 * Australian bank account (BSB + account number) for direct-entry tokens.
 * The account number is kept out of dumps the same way as CardDetails.
 */
final class BankAccount
{
    private readonly string $masked;

    public function __construct(
        public readonly string $bsb,
        #[SensitiveParameter] string $account,
        public readonly ?string $name = null,
    ) {
        if (! preg_match('/^\d{3}-?\d{3}$/', $bsb)) {
            throw new InvalidArgumentException('BSB must be 6 digits.');
        }
        if (! preg_match('/^\d{1,10}$/', $account)) {
            throw new InvalidArgumentException('Account number must be 1-10 digits.');
        }

        $this->masked = '...'.substr($account, -3);

        Secrets::put($this, ['account' => $account]);
    }

    /** e.g. "...678" */
    public function masked(): string
    {
        return $this->masked;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'bsb' => str_replace('-', '', $this->bsb),
            'account' => Secrets::get($this, 'account'),
            'name' => $this->name,
        ], fn ($v) => $v !== null);
    }

    public function __clone(): void
    {
        throw new LogicException('BankAccount cannot be cloned.');
    }

    public function __serialize(): array
    {
        throw new LogicException('BankAccount cannot be serialised.');
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public function __unserialize(array $data): void
    {
        throw new LogicException('BankAccount cannot be unserialised.');
    }
}
