# Changelog

## 2.0.1 - unreleased

### Fixed

- Card numbers and CVNs were visible to Symfony VarDumper, so `dd()`, `dump()`, Laravel's error page and Telescope showed them in full; so did `var_export()` and an `(array)` cast. They are no longer stored as object properties. `BankAccount` account numbers likewise.
- Lookups were retried once, not twice as documented (`retry()` counts attempts). They are now also retried on 502/503/504.
- On older Laravel 12 releases a connection dropped mid-request could surface as a raw Guzzle exception instead of `ConnectionException`.
- A 2xx response with a non-JSON body, or a transaction with no response code, read as a decline. Both now throw `UnexpectedResponseException`.
- A webhook naming a transaction or token that doesn't exist threw `NotFoundException` (a 500 in the documented route); it now throws `InvalidWebhookException`.
- One malformed date or expiry in a response made the whole response unreadable; that field is now `null`.
- `cursor()` could loop forever if the API repeated a `continueFrom` value.
- HTTP 403 is now an `AuthenticationException`.

### Added

- Validation of webhook URLs (https, port 443), page size, surcharge, BSB and account numbers, and amounts too large for an integer.

### Changed

- `CardDetails` and `BankAccount` can no longer be cloned (a clone would not carry the card number). `BankAccount::masked()` added.
- `guzzlehttp/guzzle` is a declared dependency (it was already required by `illuminate/http`).

## 2.0.0 - 2026-09-23

Rewrite for MerchantSuite API v5 and Laravel 12/13. See [UPGRADING.md](UPGRADING.md).

- AuthKey checkout (`checkout()`) for iframe-field payments that keep card data off your servers
- `chargeToken()` for server-side charges against a stored card
- Refunds, pre-auth capture and reversal
- Token add, find, update, delete, search and tokenise-from-transaction, plus the token AuthKey flow
- Transaction and token search with lazy pagination (`cursor()`)
- Webhook parsing and verification by re-fetch
- Typed exceptions with field-level validation errors
- Test mode on by default; payments never retried
- Weekly check against the published OpenAPI spec

## 1.0.6 - 2018-12-12

Last 1.x release (API v2, Laravel 5.6).
