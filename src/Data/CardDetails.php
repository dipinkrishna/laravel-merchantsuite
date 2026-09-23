<?php

namespace DK\MerchantSuite\Data;

use DK\MerchantSuite\Support\Secrets;
use InvalidArgumentException;
use LogicException;
use SensitiveParameter;

/**
 * Raw card data. Anything that builds one of these is in PCI DSS scope.
 * Prefer the AuthKey checkout with MerchantSuite's iframe fields, which
 * keeps card numbers off your servers entirely.
 *
 * The number and CVN are not properties of this object, so they do not
 * show up in dd(), dump(), var_dump(), var_export(), print_r(), Laravel's
 * error page, an (array) cast or json_encode(). The constructor arguments
 * are redacted from stack traces, and the object refuses to serialise.
 */
final class CardDetails
{
    public readonly Expiry $expiry;

    private readonly string $masked;

    public function __construct(
        #[SensitiveParameter] string $number,
        Expiry|string $expiry,
        #[SensitiveParameter] ?string $cvn = null,
        public readonly ?string $name = null,
    ) {
        $digits = preg_replace('/[\s-]/', '', $number) ?? '';
        if (! preg_match('/^\d{12,19}$/', $digits)) {
            throw new InvalidArgumentException('Card number must be 12-19 digits.');
        }
        if ($cvn !== null && ! preg_match('/^\d{3,4}$/', $cvn)) {
            throw new InvalidArgumentException('CVN must be 3 or 4 digits.');
        }

        $this->expiry = $expiry instanceof Expiry ? $expiry : Expiry::fromString($expiry);
        $this->masked = substr($digits, 0, 6).'...'.substr($digits, -3);

        Secrets::put($this, ['number' => $digits, 'cvn' => $cvn]);
    }

    /** e.g. "512345...346" */
    public function masked(): string
    {
        return $this->masked;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(bool $withCvn = true): array
    {
        return array_filter([
            'number' => Secrets::get($this, 'number'),
            'expiry' => $this->expiry->toArray(),
            'cvn' => $withCvn ? Secrets::get($this, 'cvn') : null,
            'name' => $this->name,
        ], fn ($v) => $v !== null);
    }

    public function __clone(): void
    {
        throw new LogicException('CardDetails cannot be cloned.');
    }

    public function __serialize(): array
    {
        throw new LogicException('CardDetails cannot be serialised.');
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public function __unserialize(array $data): void
    {
        throw new LogicException('CardDetails cannot be unserialised.');
    }
}
