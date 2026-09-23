<?php

namespace DK\MerchantSuite\Resources;

use DK\MerchantSuite\Data\CardDetails;
use DK\MerchantSuite\Data\SearchResults;
use DK\MerchantSuite\Data\Transaction;
use DK\MerchantSuite\Data\TransactionDetails;
use DK\MerchantSuite\Enums\Action;
use DK\MerchantSuite\Enums\SubType;
use DK\MerchantSuite\Enums\TransactionType;
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
        return self::transactionFrom($this->client->post('txns', [
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
        return self::transactionFrom($this->client->get('txns/'.self::segment($txnNumber)));
    }

    /**
     * Fetch the result of an AuthKey payment from the resultKey the browser
     * was redirected back with.
     */
    public function result(string $resultKey): Transaction
    {
        return self::transactionFrom($this->client->get('txns/resultkeys/'.self::segment($resultKey)));
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
        return $this->searchPage('txns/search', 'transactions', Transaction::fromArray(...), $filters, $perPage, $continueFrom);
    }

    /**
     * Every matching transaction, fetching pages as you iterate.
     *
     * @param  array<string, mixed>  $filters
     * @return LazyCollection<int, Transaction>
     */
    public function cursor(array $filters = [], int $perPage = 100): LazyCollection
    {
        return self::walk(fn (?string $continueFrom) => $this->search($filters, $perPage, $continueFrom));
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

        return self::transactionFrom($this->client->post('txns', $details->toTwoPartyArray($this->defaults)));
    }
}
