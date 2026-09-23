<?php

namespace DK\MerchantSuite\Data;

use InvalidArgumentException;

final readonly class Expiry
{
    /** Two-digit month, "01".."12" (or "99" to simulate a bank response code in test mode). */
    public string $month;

    /** Two-digit year, e.g. "29". */
    public string $year;

    public function __construct(string|int $month, string|int $year)
    {
        $month = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $year = (string) $year;
        $year = strlen($year) === 4 ? substr($year, 2) : str_pad($year, 2, '0', STR_PAD_LEFT);

        if (! preg_match('/^(0[1-9]|1[0-2]|99)$/', $month)) {
            throw new InvalidArgumentException('Expiry month must be 01-12.');
        }
        if (! preg_match('/^\d{2}$/', $year)) {
            throw new InvalidArgumentException('Expiry year must be two or four digits.');
        }

        $this->month = $month;
        $this->year = $year;
    }

    /**
     * Accepts "MMYY", "MM/YY", "MM-YY" or "MM/YYYY".
     */
    public static function fromString(string $expiry): self
    {
        if (! preg_match('/^\s*(\d{1,2})\s*[\/\-]?\s*(\d{2}|\d{4})\s*$/', $expiry, $m)) {
            throw new InvalidArgumentException('Expiry must look like MMYY or MM/YY.');
        }

        return new self($m[1], $m[2]);
    }

    /**
     * Lenient on purpose: this reads gateway responses, and one odd expiry
     * should not make the whole transaction unreadable.
     *
     * @param  array<string, string>|null  $data
     */
    public static function fromArray(?array $data): ?self
    {
        if (! isset($data['month'], $data['year'])) {
            return null;
        }

        try {
            return new self($data['month'], $data['year']);
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    /**
     * @return array{month: string, year: string}
     */
    public function toArray(): array
    {
        return ['month' => $this->month, 'year' => $this->year];
    }

    public function __toString(): string
    {
        return $this->month.'/'.$this->year;
    }
}
