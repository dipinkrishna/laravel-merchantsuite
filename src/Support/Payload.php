<?php

namespace DK\MerchantSuite\Support;

/**
 * Typed reads from a decoded JSON response. The gateway's JSON is untrusted
 * input: a field can be missing, null, or a different scalar type than the
 * spec says, and none of those should become a TypeError deep in a DTO.
 *
 * @internal
 */
final readonly class Payload
{
    /**
     * @param  array<array-key, mixed>  $data
     */
    public function __construct(public array $data) {}

    /**
     * @param  array<array-key, mixed>|null  $data
     */
    public static function of(?array $data): self
    {
        return new self($data ?? []);
    }

    public function str(string $key): ?string
    {
        $value = $this->data[$key] ?? null;

        return is_scalar($value) && $value !== '' && ! is_bool($value) ? (string) $value : null;
    }

    public function int(string $key): ?int
    {
        $value = $this->data[$key] ?? null;

        return is_int($value) || (is_string($value) && preg_match('/^-?\d+$/', $value)) ? (int) $value : null;
    }

    public function bool(string $key): ?bool
    {
        $value = $this->data[$key] ?? null;

        return is_bool($value) ? $value : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function arr(string $key): ?array
    {
        $value = $this->data[$key] ?? null;

        if (! is_array($value) || $value === []) {
            return null;
        }

        /** @var array<string, mixed> $value */
        return $value;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(string $key): array
    {
        $value = $this->data[$key] ?? null;
        if (! is_array($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $item) {
            if (is_array($item)) {
                /** @var array<string, mixed> $item */
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return array<string, string>|null
     */
    public function strings(string $key): ?array
    {
        $value = $this->arr($key);
        if ($value === null) {
            return null;
        }

        $out = [];
        foreach ($value as $k => $v) {
            if (is_scalar($v) && ! is_bool($v)) {
                $out[(string) $k] = (string) $v;
            }
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        /** @var array<string, mixed> */
        return $this->data;
    }
}
