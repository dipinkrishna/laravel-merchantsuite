<?php

namespace DK\MerchantSuite\Resources;

use DK\MerchantSuite\Data\CardDetails;
use DK\MerchantSuite\Data\SearchResults;
use DK\MerchantSuite\Data\Transaction;
use DK\MerchantSuite\Data\TransactionDetails;
use DK\MerchantSuite\Enums\Action;
use DK\MerchantSuite\Enums\SubType;
use DK\MerchantSuite\Enums\TransactionType;
use DK\MerchantSuite\Support\Payload;
use Illuminate\Support\LazyCollection;

class Transactions extends Resource
{
    /**
     * 2-party payment: your server sends the card number to the gateway.
     *
     * This puts your servers in full PCI DSS scope. Use checkout() with the
     * iframe fields, or chargeToken(), unless you have a reason not to.
     */
    public function process(TransactionDetails $details, CardDetails $card): Transaction
    {
        return Transaction::fromArray($this->client->post('txns', [
            ...$details->toTwoPartyArray($this->defaults),
            'cardDetails' => $card->toArray(),
        ]));
    }

    /**
     * Refund all or part of an earlier payment.
     */
    public function refund(string $txnNumber, int $amount, string $crn1, ?string $currency = null, ?string $merchantReference = null): Transaction
    {
        return $this->followUp(Action::Refund, $txnNumber, $amount, $crn1, $currency, $merchantReference);
    }

    /**
     * Capture funds held by an earlier PreAuth.
     */
    public function capture(string $txnNumber, int $amount, string $crn1, ?string $currency = null, ?string $merchantReference = null): Transaction
    {
        return $this->followUp(Action::Capture, $txnNumber, $amount, $crn1, $currency, $merchantReference);
    }

    /**
     * Release an uncaptured PreAuth.
     */
    public function reverse(string $txnNumber, int $amount, string $crn1, ?string $currency = null, ?string $merchantReference = null): Transaction
    {
        return $this->followUp(Action::Reversal, $txnNumber, $amount, $crn1, $currency, $merchantReference);
    }

    public function find(string $txnNumber): Transaction
    {
        return Transaction::fromArray($this->client->get('txns/'.self::segment($txnNumber)));
    }

    /**
     * Fetch the result of an AuthKey payment from the resultKey the browser
     * was redirected back with.
     */
    public function result(string $resultKey): Transaction
    {
        return Transaction::fromArray($this->client->get('txns/resultkeys/'.self::segment($resultKey)));
    }

    /**
     * One page of transactions matching the filters. Keys are the API's
     * TxnSearchRequest fields: crn1, merchantReference, fromDate, toDate
     * (YYYY-MM-DD), txnNumber, responseCode, token, ...
     *
     * @param  array<string, mixed>  $filters
     * @return SearchResults<Transaction>
     */
    public function search(array $filters = [], int $perPage = 100, ?string $continueFrom = null): SearchResults
    {
        $response = $this->client->post('txns/search', array_filter([
            ...$filters,
            'numberOfRecords' => $perPage,
            'continueFrom' => $continueFrom,
        ], fn ($v) => $v !== null));

        $p = Payload::of($response);

        return new SearchResults(
            array_map(Transaction::fromArray(...), $p->list('transactions')),
            $p->str('continueFrom'),
            $p->int('resultCount') ?? 0,
        );
    }

    /**
     * Every matching transaction, fetching pages as you iterate.
     *
     * @param  array<string, mixed>  $filters
     * @return LazyCollection<int, Transaction>
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

    private function followUp(Action $action, string $txnNumber, int $amount, string $crn1, ?string $currency, ?string $merchantReference): Transaction
    {
        $details = new TransactionDetails(
            amount: $amount,
            crn1: $crn1,
            action: $action,
            type: TransactionType::Internet,
            subType: SubType::Single,
            currency: $currency,
            merchantReference: $merchantReference,
            originalTxnNumber: $txnNumber,
        );

        return Transaction::fromArray($this->client->post('txns', $details->toTwoPartyArray($this->defaults)));
    }
}
