<?php

namespace DK\MerchantSuite\Resources;

use Closure;
use DK\MerchantSuite\Data\SearchResults;
use DK\MerchantSuite\Data\Token;
use DK\MerchantSuite\Data\Transaction;
use DK\MerchantSuite\Exceptions\UnexpectedResponseException;
use DK\MerchantSuite\Http\Client;
use DK\MerchantSuite\Support\Payload;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;

abstract class Resource
{
    /**
     * @param  array{currency?: string|null, biller_code?: string|null, test_mode?: bool}  $defaults
     */
    public function __construct(
        protected readonly Client $client,
        protected readonly array $defaults = [],
    ) {}

    /**
     * @param  array<array-key, mixed>  $response
     */
    protected static function authkeyFrom(array $response): string
    {
        $authkey = Payload::of($response)->str('authkey');

        if ($authkey === null) {
            throw new UnexpectedResponseException('MerchantSuite did not return an authkey.', 200);
        }

        return $authkey;
    }

    /**
     * A transaction with no response code cannot be read as approved or
     * declined, so it must not reach isApproved() as a silent decline.
     *
     * @param  array<array-key, mixed>  $response
     */
    protected static function transactionFrom(array $response): Transaction
    {
        $txn = Transaction::fromArray($response);

        if ($txn->responseCode === null) {
            throw new UnexpectedResponseException('MerchantSuite returned a transaction without a response code.', 200);
        }

        return $txn;
    }

    /**
     * @param  array<array-key, mixed>  $response
     */
    protected static function tokenFrom(array $response): Token
    {
        $token = Token::fromArray($response);

        if ($token->token === '') {
            throw new UnexpectedResponseException('MerchantSuite returned a token record without a token.', 200);
        }

        return $token;
    }

    /**
     * MerchantSuite only calls webhooks over HTTPS on port 443.
     *
     * @return array{url: string}|null
     */
    protected static function webhook(?string $url): ?array
    {
        if ($url === null) {
            return null;
        }

        $parts = parse_url($url);
        if (($parts['scheme'] ?? null) !== 'https' || ! isset($parts['host']) || ! in_array($parts['port'] ?? 443, [443], true)) {
            throw new InvalidArgumentException('Webhook URL must be https:// on port 443; MerchantSuite will not call anything else.');
        }

        return ['url' => $url];
    }

    /**
     * @template T
     *
     * @param  array<string, mixed>  $filters
     * @param  Closure(array<string, mixed>): T  $map
     * @return SearchResults<T>
     */
    protected function searchPage(string $path, string $key, Closure $map, array $filters, int $perPage, ?string $continueFrom): SearchResults
    {
        if ($perPage < 1) {
            throw new InvalidArgumentException('perPage must be at least 1.');
        }

        $p = Payload::of($this->client->post($path, array_filter([
            ...$filters,
            'numberOfRecords' => $perPage,
            'continueFrom' => $continueFrom,
        ], fn ($v) => $v !== null)));

        return new SearchResults(
            array_map($map, $p->list($key)),
            $p->str('continueFrom'),
            $p->int('resultCount') ?? 0,
        );
    }

    /**
     * Walk every page lazily. Stops on an empty page, a missing cursor, or a
     * cursor the API has already returned, so it cannot loop forever.
     *
     * @template T
     *
     * @param  Closure(?string): SearchResults<T>  $page
     * @return LazyCollection<int, T>
     */
    protected static function walk(Closure $page): LazyCollection
    {
        return LazyCollection::make(function () use ($page) {
            $seen = [];
            $continueFrom = null;

            do {
                $results = $page($continueFrom);

                // Plain yields: `yield from` would reuse each page's 0..n keys.
                foreach ($results->items as $item) {
                    yield $item;
                }

                $continueFrom = $results->continueFrom;
                if ($continueFrom !== null && isset($seen[$continueFrom])) {
                    break;
                }
                $seen[(string) $continueFrom] = true;
            } while ($results->hasMore() && $results->count() > 0);
        });
    }

    protected static function segment(string $value): string
    {
        return rawurlencode($value);
    }
}
