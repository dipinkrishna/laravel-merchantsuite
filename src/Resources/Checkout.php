<?php

namespace DK\MerchantSuite\Resources;

use DK\MerchantSuite\Data\Expiry;
use DK\MerchantSuite\Data\PaymentMethod;
use DK\MerchantSuite\Data\Transaction;
use DK\MerchantSuite\Data\TransactionDetails;
use InvalidArgumentException;

/**
 * The AuthKey payment flow. Card numbers go from the browser straight to
 * MerchantSuite through their iframe fields, so your server never sees
 * them (PCI SAQ-A).
 *
 *   1. create()    server: open a session and attach the amount
 *   2. browser:    MerchantSuite.txn.authkey.setupIframeFields(authkey, ...)
 *   3. process()   server: charge whatever card the browser attached
 */
class Checkout extends Resource
{
    /**
     * Returns the authkey to hand to the browser.
     */
    public function create(TransactionDetails $details): string
    {
        $authkey = self::authkeyFrom($this->client->post('txns/authkeys'));

        $this->client->put('txns/authkeys/'.self::segment($authkey).'/txn-details', $details->toAuthkeyArray($this->defaults));

        return $authkey;
    }

    /**
     * Pay with a stored token instead of a card from the browser. Returns
     * the masked card so the customer can confirm it. Pass $expiry if the
     * card on file has since been reissued, and $cvn if you collect it.
     */
    public function attachToken(string $authkey, string $token, ?Expiry $expiry = null, ?string $cvn = null): PaymentMethod
    {
        $card = array_filter([
            'expiry' => $expiry?->toArray(),
            'cvn' => $cvn,
        ]);

        return PaymentMethod::fromArray($this->client->put(
            'txns/authkeys/'.self::segment($authkey).'/payment-method',
            array_filter(['token' => $token, 'card' => $card ?: null]),
        )) ?? new PaymentMethod($token, null, null, null);
    }

    /**
     * What the browser attached: masked card, whether biller rules accept
     * it, and any surcharge to show before charging.
     */
    public function paymentMethod(string $authkey): ?PaymentMethod
    {
        return PaymentMethod::fromArray($this->client->get('txns/authkeys/'.self::segment($authkey).'/payment-method'));
    }

    /**
     * @param  bool|int|null  $surcharge  true to have the gateway calculate it from biller rules, or a fixed amount in cents
     */
    public function process(string $authkey, ?string $webhookUrl = null, bool|int|null $surcharge = null, bool $updateTokenExpiry = false): Transaction
    {
        if (is_int($surcharge) && $surcharge < 0) {
            throw new InvalidArgumentException('Surcharge cannot be negative.');
        }

        return self::transactionFrom($this->client->post(
            'txns/authkeys/'.self::segment($authkey).'/process',
            array_filter([
                'webhook' => self::webhook($webhookUrl),
                'surcharge' => match (true) {
                    $surcharge === true => ['calculate' => true],
                    is_int($surcharge) => ['amount' => $surcharge],
                    default => null,
                },
                'updateToken' => $updateTokenExpiry ?: null,
            ]),
        ));
    }
}
