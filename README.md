# laravel-merchantsuite

[![tests](https://github.com/dipinkrishna/laravel-merchantsuite/actions/workflows/tests.yml/badge.svg)](https://github.com/dipinkrishna/laravel-merchantsuite/actions/workflows/tests.yml)
[![spec drift](https://github.com/dipinkrishna/laravel-merchantsuite/actions/workflows/spec-drift.yml/badge.svg)](https://github.com/dipinkrishna/laravel-merchantsuite/actions/workflows/spec-drift.yml)

A Laravel client for the [MerchantSuite](https://www.merchantsuite.com/developerzone/v5/) (Linkly) payments API v5: card payments, refunds and pre-auths, stored card tokens, the PCI-friendly AuthKey checkout, and webhooks.

- Typed request and response objects, integer amounts, enums for every API code
- Card numbers kept out of `dd()`, error pages, logs, serialisation and stack traces
- Declines are results, not exceptions; gateway errors are typed exceptions with field-level detail
- A proxy or maintenance page is never mistaken for a declined card
- Payments are never retried automatically, so a timeout cannot charge a card twice
- Webhooks are verified by re-fetching from the API, since MerchantSuite does not sign them
- Checked weekly against MerchantSuite's published OpenAPI spec

> **Status: built against the published v5 spec, not yet run against a live account.**
> MerchantSuite's free trial does not include API access, so every request and response in the test suite is modelled on the [OpenAPI spec](https://www.merchantsuite.com/rest/v5/swagger) and docs. The weekly [spec check](scripts/check-spec.php) catches changes to the parts of the API the package uses. If you have API credentials and try it, an issue with what you saw is very welcome.

## Requirements

- PHP 8.3+
- Laravel 12 or 13
- A MerchantSuite facility with API access (Checkout or Enterprise)

## Install

```bash
composer require dipinkrishna/laravel-merchantsuite
```

Create an API user in the MerchantSuite back office under **Settings > User Management**, with the **API** permission, then add to `.env`:

```dotenv
MERCHANTSUITE_USERNAME=api-user
MERCHANTSUITE_MERCHANT_NUMBER=5353109000000000
MERCHANTSUITE_PASSWORD=...
MERCHANTSUITE_BILLER_CODE=1234567   # optional
MERCHANTSUITE_TEST_MODE=true        # set to false in production
```

`MERCHANTSUITE_TEST_MODE` defaults to **true**, so a missing variable can never charge a real card. Production has to opt out explicitly.

To publish the config file:

```bash
php artisan vendor:publish --tag=merchantsuite-config
```

## Amounts

The API works in the currency's smallest unit: `1999` is $19.99. Use `Amount::fromDecimal()` to convert a string without float rounding (`(int) (4.35 * 100)` is `434`):

```php
use DK\MerchantSuite\Data\Amount;

Amount::fromDecimal('19.99');   // 1999
Amount::toDecimal(1999);        // "19.99"
```

## Taking a payment: AuthKey checkout (recommended)

The card number goes from the customer's browser straight to MerchantSuite through their iframe fields. Your server never sees it, which keeps you at PCI DSS SAQ-A.

**1. Server: open a checkout session**

```php
use DK\MerchantSuite\Data\TransactionDetails;
use DK\MerchantSuite\Enums\TokenisationMode;
use DK\MerchantSuite\Facades\MerchantSuite;

$authkey = MerchantSuite::checkout()->create(new TransactionDetails(
    amount: 4999,
    crn1: $order->number,
    emailAddress: $order->email,
    tokenisationMode: TokenisationMode::OptIn, // let the customer save the card
));

return view('checkout', ['authkey' => $authkey]);
```

**2. Browser: collect the card**

```html
<div id="cardNumberField"></div>
<div id="expiryMonthField"></div>
<div id="expiryYearField"></div>
<div id="cvnField"></div>
<div id="nameOnCardField"></div>
<button id="pay">Pay</button>

<script src="https://www.merchantsuite.com/rest/clientscripts/api.js"></script>
<script>
MerchantSuite.txn.authkey.setupIframeFields(@json($authkey), {
    card: {
        number: { selector: '#cardNumberField' },
        expiry: { month: { selector: '#expiryMonthField' }, year: { selector: '#expiryYearField' } },
        cvn: { selector: '#cvnField' },
        name: { selector: '#nameOnCardField' },
    },
    onFormLoaded(controller) {
        document.querySelector('#pay').addEventListener('click', () => {
            controller.submit((code) => {
                if (code === 'success') {
                    fetch('/checkout/process', { method: 'POST', /* send authkey + CSRF */ });
                }
            });
        });
    },
});
</script>
```

**3. Server: charge it**

```php
$txn = MerchantSuite::checkout()->process($authkey, webhookUrl: route('merchantsuite.webhook'));

if ($txn->isApproved()) {
    $order->markPaid($txn->txnNumber, $txn->receiptNumber);
    $customer->update(['card_token' => $txn->token()]); // set when the customer opted in
} else {
    return back()->withErrors(['card' => $txn->responseText]);
}
```

Before processing you can read back what the browser attached, e.g. to show a surcharge:

```php
$method = MerchantSuite::checkout()->paymentMethod($authkey);
$method->card->number;   // "512345...346"
$method->surcharge;      // cents, from your biller rules
```

## Charging a saved card

No card data is involved, so this is safe to run from a queued job:

```php
$txn = MerchantSuite::chargeToken($customer->card_token, new TransactionDetails(
    amount: 2500,
    crn1: $invoice->number,
    subType: \DK\MerchantSuite\Enums\SubType::Recurring,
));
```

## Refunds, pre-auths and captures

```php
use DK\MerchantSuite\Enums\Action;

MerchantSuite::transactions()->refund($txn->txnNumber, amount: 500, crn1: $order->number);

// Hold funds, then capture or release them
$hold = MerchantSuite::chargeToken($token, new TransactionDetails(10000, 'BOOKING-7', action: Action::PreAuth));
MerchantSuite::transactions()->capture($hold->txnNumber, 8500, 'BOOKING-7');
MerchantSuite::transactions()->reverse($hold->txnNumber, 10000, 'BOOKING-7');
```

## Looking things up

```php
MerchantSuite::transactions()->find('100000000123');
MerchantSuite::transactions()->result($resultKey); // after a redirect-based checkout

// One page
$page = MerchantSuite::transactions()->search(['crn1' => 'ORDER-1001', 'fromDate' => '2026-09-01']);

// Every page, fetched lazily as you iterate
MerchantSuite::transactions()
    ->cursor(['fromDate' => '2026-09-01', 'toDate' => '2026-09-30'])
    ->filter->isApproved()
    ->sum('amount');
```

## Tokens

```php
use DK\MerchantSuite\Data\TokenDetails;

$token = MerchantSuite::tokens()->find($customer->card_token);
$token->card->expiry;           // "05/29"

MerchantSuite::tokens()->fromTransaction($txn->txnNumber);  // save the card from a past payment
MerchantSuite::tokens()->delete($customer->card_token);

// Cards expiring soon
MerchantSuite::tokens()->cursor(['expiry' => ['month' => '10', 'year' => '26']]);
```

To let a customer add or replace a saved card without a payment, use a token AuthKey with the same iframe fields (`MerchantSuite.token.authkey.setupIframeFields` in the browser):

```php
$authkey = MerchantSuite::tokens()->createAuthkey(new TokenDetails(crn1: $customer->id), existingToken: $customer->card_token);
// ... browser attaches the card ...
$token = MerchantSuite::tokens()->processAuthkey($authkey);
```

## Webhooks

MerchantSuite does not sign webhooks, so a request to your webhook URL proves nothing on its own. `verify()` takes the transaction number or token from the body and fetches the real record from the API:

```php
use DK\MerchantSuite\Exceptions\InvalidWebhookException;

Route::post('/webhooks/merchantsuite', function (Request $request) {
    try {
        $event = MerchantSuite::webhooks()->verify($request);
    } catch (InvalidWebhookException) {
        return response()->noContent(400);
    }

    // Deliveries retry hourly for 24 hours and can repeat.
    if (! Cache::add('ms-webhook:'.$event->idempotencyKey(), true, now()->addDays(2))) {
        return response()->noContent();
    }

    if ($event->isTransaction() && $event->data->isApproved()) {
        Order::where('number', $event->data->crn1)->first()?->markPaid($event->data->txnNumber);
    }

    return response()->noContent();
})->withoutMiddleware(VerifyCsrfToken::class);
```

A webhook for a transaction or token MerchantSuite doesn't have also throws `InvalidWebhookException`, so a forged request gets a 400 rather than a 500.

Webhook URLs passed to `process()` must be `https://` on port 443, the only thing MerchantSuite will call; anything else throws before the request is sent.

Set `MERCHANTSUITE_WEBHOOK_VERIFY_IP=true` to also reject requests that don't come from MerchantSuite's published addresses. Only turn it on if `$request->ip()` returns real client IPs (TrustProxies configured behind a load balancer or CDN).

## 2-party (card data through your server)

Supported, but it puts your servers in full PCI DSS scope. Use it only if you already are.

```php
use DK\MerchantSuite\Data\CardDetails;

$txn = MerchantSuite::transactions()->process(
    new TransactionDetails(amount: 1999, crn1: 'ORDER-1'),
    new CardDetails('5123456789012346', '05/29', cvn: '123', name: 'Jane Citizen'),
);
```

The number and CVN are not stored as properties of `CardDetails`, so `dd()`, `dump()`, Laravel's error page, Telescope, `var_export()` and `json_encode()` only ever see the masked number (`512345...346`, also `$card->masked()`). The object refuses to be cloned or serialised (so it cannot land in a queue payload or cache), and its constructor arguments are marked `#[SensitiveParameter]`, so they are redacted from stack traces. `BankAccount` works the same way.

## Errors

| Situation | What you get |
|---|---|
| Card declined | a `Transaction` with `isApproved() === false`, `responseText`, `bankResponseCode` |
| Bad input | `ValidationException`; `->fieldErrors()` gives `['crn1' => ['Crn1 is required']]` |
| Wrong credentials or no API permission | `AuthenticationException` |
| Unknown txn/token | `NotFoundException` |
| Other gateway errors | `ApiException` with `->status`, `->errorCode` (enum), `->details` |
| Timeout / network | `ConnectionException` |
| 2xx that isn't a real answer (HTML page, transaction with no response code) | `UnexpectedResponseException` |
| Missing config | `ConfigurationException` |

All of them extend `MerchantSuiteException`.

**A `ConnectionException` or `UnexpectedResponseException` on a payment means the outcome is unknown.** The gateway may have charged the card before the connection dropped. `ConnectionException::outcomeUnknown()` is true for anything but a GET. Search by your `crn1` before trying again:

```php
try {
    $txn = MerchantSuite::chargeToken($token, $details);
} catch (ConnectionException|UnexpectedResponseException $e) {
    $txn = MerchantSuite::transactions()->search(['crn1' => $details->crn1])->items[0] ?? null;
}
```

Lookups (GET) are retried up to twice on connection errors and 502/503/504. Nothing else is retried.

## Testing your app

Everything goes through Laravel's HTTP client, so `Http::fake()` works:

```php
Http::fake([
    'www.merchantsuite.com/rest/v5/txns/authkeys' => Http::response(['authkey' => 'ak-1'], 201),
    'www.merchantsuite.com/rest/v5/txns/authkeys/ak-1/*' => Http::response(['txn' => [
        'txnNumber' => '1', 'responseCode' => '0', 'amount' => 2500,
    ]]),
]);
```

Against a real account in test mode, MerchantSuite simulates bank responses from the amount (the last two digits become the bank response code, so `10005` returns `05`) or from an expiry of `99xx`. Test cards and the full list are on their [test mode page](https://www.merchantsuite.com/developerzone/v5/reference/test-mode).

## Development

```bash
composer test          # Pest
composer analyse       # PHPStan, level max
composer lint          # Pint
composer check-spec    # compare against the live OpenAPI spec
```

## Upgrading from 1.x

See [UPGRADING.md](UPGRADING.md). 2.0 is a rewrite for API v5 and Laravel 12+; nothing from 1.x carries over.

## License

MIT
