<?php

namespace DK\MerchantSuite\Resources;

use DK\MerchantSuite\Data\BankAccount;
use DK\MerchantSuite\Data\CardDetails;
use DK\MerchantSuite\Data\SearchResults;
use DK\MerchantSuite\Data\Token;
use DK\MerchantSuite\Data\TokenDetails;
use DK\MerchantSuite\Support\Payload;
use Illuminate\Support\LazyCollection;

class Tokens extends Resource
{
    /**
     * 2-party: store a card or bank account sent from your server. Puts you
     * in PCI DSS scope for cards; see createAuthkey() for the iframe route.
     */
    public function add(TokenDetails $details, CardDetails|BankAccount $method): Token
    {
        return Token::fromArray($this->client->post('tokens', [
            ...$details->toArray(),
            ...self::method($method),
        ]));
    }

    public function find(string $token): Token
    {
        return Token::fromArray($this->client->get('tokens/'.self::segment($token)));
    }

    public function update(string $token, TokenDetails $details, CardDetails|BankAccount $method): Token
    {
        return Token::fromArray($this->client->put('tokens/'.self::segment($token), [
            ...$details->toArray(),
            ...self::method($method),
        ]));
    }

    public function delete(string $token): void
    {
        $this->client->delete('tokens/'.self::segment($token));
    }

    /**
     * Create a token from the card used on an earlier transaction.
     */
    public function fromTransaction(string $txnNumber): Token
    {
        return Token::fromArray($this->client->post('tokens/txn/'.self::segment($txnNumber)));
    }

    /**
     * Start a token AuthKey session for the iframe fields: the browser
     * attaches the card, then you call processAuthkey(). Pass $existingToken
     * to update a stored card instead of adding one.
     */
    public function createAuthkey(TokenDetails $details, ?string $existingToken = null): string
    {
        $authkey = self::authkeyFrom($this->client->post('tokens/authkeys', array_filter(['token' => $existingToken])));

        $this->client->put('tokens/authkeys/'.self::segment($authkey).'/token-details', $details->toArray());

        return $authkey;
    }

    public function processAuthkey(string $authkey, ?string $webhookUrl = null): Token
    {
        return Token::fromArray($this->client->post(
            'tokens/authkeys/'.self::segment($authkey).'/process',
            array_filter(['webhook' => $webhookUrl ? ['url' => $webhookUrl] : null]),
        ));
    }

    /**
     * @param  array<string, mixed>  $filters  TokenSearchRequest fields: crn1, emailAddress, expiredCardsOnly, createdFrom, ...
     * @return SearchResults<Token>
     */
    public function search(array $filters = [], int $perPage = 100, ?string $continueFrom = null): SearchResults
    {
        $response = $this->client->post('tokens/search', array_filter([
            ...$filters,
            'numberOfRecords' => $perPage,
            'continueFrom' => $continueFrom,
        ], fn ($v) => $v !== null));

        $p = Payload::of($response);

        return new SearchResults(
            array_map(Token::fromArray(...), $p->list('tokens')),
            $p->str('continueFrom'),
            $p->int('resultCount') ?? 0,
        );
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LazyCollection<int, Token>
     */
    public function cursor(array $filters = [], int $perPage = 100): LazyCollection
    {
        return LazyCollection::make(function () use ($filters, $perPage) {
            $continueFrom = null;
            do {
                $page = $this->search($filters, $perPage, $continueFrom);
                // Plain yields: `yield from` would reuse each page's 0..n keys.
                foreach ($page->items as $item) {
                    yield $item;
                }
                $continueFrom = $page->continueFrom;
            } while ($page->hasMore() && $page->count() > 0);
        });
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function method(CardDetails|BankAccount $method): array
    {
        // Tokens store no CVN.
        return $method instanceof CardDetails
            ? ['card' => $method->toArray(withCvn: false)]
            : ['bank' => $method->toArray()];
    }
}
