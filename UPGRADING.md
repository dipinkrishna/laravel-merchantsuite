# Upgrading from 1.x to 2.0

2.0 is a rewrite. 1.x targeted MerchantSuite API v2 (`/api/v2`) and Laravel 5.6; 2.0 targets API v5 (`/rest/v5`) and Laravel 12 and 13. There is no compatibility layer.

## Config

1.x read `config('services.merchantsuite')` with `username`, `password`, `membershipID`, `mode`. 2.0 has its own `config/merchantsuite.php`, filled from env:

| 1.x (`services.merchantsuite`) | 2.0 env |
|---|---|
| `username` | `MERCHANTSUITE_USERNAME` |
| `password` | `MERCHANTSUITE_PASSWORD` |
| `membershipID` | `MERCHANTSUITE_MERCHANT_NUMBER` |
| `mode` = `test` | `MERCHANTSUITE_TEST_MODE=true` (the default) |

## Payments

```php
// 1.x
(new MerchantSuite('live'))->performTransaction([
    'amount' => 19.99,
    'currency' => 'AUD',
    'Reference1' => 'ORDER-1',
    'CardDetails' => ['Token' => $token],
]);

// 2.0
MerchantSuite::chargeToken($token, new TransactionDetails(amount: 1999, crn1: 'ORDER-1'));
```

- Amounts are integer cents. 1.x multiplied a float by 100, which could come out a cent short (`19.99 * 100` is `1998.9999...`).
- `Reference1/2/3` are `crn1/2/3`. 1.x sent `Reference1` in all three slots.
- 1.x read the card expiry from a key misspelt `ExpixyDate`, so a correctly spelt `ExpiryDate` was silently ignored.
- For new card payments, prefer `checkout()` with MerchantSuite's iframe fields over sending card numbers from your server.

## Tokens

```php
// 1.x
$result = (new MerchantSuite)->addToken(['CardNumber' => ..., 'CardHolderName' => ..., 'ExpiryDate' => 'MMYY']);
$result['details']['Token'];

// 2.0
$token = MerchantSuite::tokens()->add(new TokenDetails('CUST-1'), new CardDetails($number, 'MM/YY', name: $name));
$token->token;
```

1.x returned `['success' => false, 'error' => ...]` on failure. 2.0 throws a `MerchantSuiteException` subclass.

## Existing tokens

Tokens are held by MerchantSuite, not the package, so tokens created through 1.x should keep working with `chargeToken()`. This has not been confirmed against a live account.
