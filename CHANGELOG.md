# Changelog

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
