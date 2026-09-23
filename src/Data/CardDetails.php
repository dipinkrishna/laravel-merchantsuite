<?php

namespace DK\MerchantSuite\Data;

use InvalidArgumentException;
use SensitiveParameter;

/**
 * Raw card data. Anything that builds one of these is in PCI DSS scope.
 * Prefer the AuthKey checkout with MerchantSuite's iframe fields, which
 * keeps card numbers off your servers entirely.
 *
 * The number and CVN are hidden from var_dump(), print_r(), serialisation
 * and stack traces.
 */
final class CardDetails
{
    public readonly Expiry $expiry;

    private readonly string $number;

    public function __construct(
        #[SensitiveParameter] string $number,
        Expiry|string $expiry,
        #[SensitiveParameter] private readonly ?string $cvn = null,
        public readonly ?string $name = null,
    ) {
        $digits = preg_replace('/[\s-]/', '', $number) ?? '';
        if (! preg_match('/^\d{12,19}$/', $digits)) {
            throw new InvalidArgumentException('Card number must be 12-19 digits.');
        }
        if ($cvn !== null && ! preg_match('/^\d{3,4}$/', $cvn)) {
            throw new InvalidArgumentException('CVN must be 3 or 4 digits.');
        }

        $this->number = $digits;
        $this->expiry = $expiry instanceof Expiry ? $expiry : Expiry::fromString($expiry);
    }

    public function masked(): string
    {
        return substr($this->number, 0, 6).str_repeat('.', 3).substr($this->number, -3);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(bool $withCvn = true): array
    {
        return array_filter([
            'number' => $this->number,
            'expiry' => $this->expiry->toArray(),
            'cvn' => $withCvn ? $this->cvn : null,
            'name' => $this->name,
        ], fn ($v) => $v !== null);
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'number' => $this->masked(),
            'expiry' => (string) $this->expiry,
            'cvn' => $this->cvn === null ? null : '***',
            'name' => $this->name,
        ];
    }

    public function __serialize(): array
    {
        throw new \LogicException('CardDetails cannot be serialised.');
    }
}
