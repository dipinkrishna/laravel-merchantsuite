<?php

/**
 * Compares the package against MerchantSuite's published OpenAPI spec.
 *
 *   php scripts/check-spec.php [path-or-url]
 *
 * Fails (exit 1) when an endpoint the package calls has gone, when an enum
 * case the package sends is no longer accepted, or when a request builder
 * stops producing a field the spec marks as required. New enum values on the
 * gateway side are reported as warnings only.
 *
 * There is no sandbox to test against, so this is the tripwire for API
 * changes. CI runs it weekly.
 */

require __DIR__.'/../vendor/autoload.php';

use DK\MerchantSuite\Data\CardDetails;
use DK\MerchantSuite\Data\TokenDetails;
use DK\MerchantSuite\Data\TransactionDetails;
use DK\MerchantSuite\Enums;

$source = $argv[1] ?? 'https://www.merchantsuite.com/rest/v5/swagger';
$json = file_get_contents($source);
if ($json === false) {
    fwrite(STDERR, "Could not read {$source}\n");
    exit(2);
}

/** @var array{paths: array<string, array<string, array<string, mixed>>>, definitions: array<string, array<string, mixed>>} $spec */
$spec = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

$errors = [];
$warnings = [];

// Endpoints the package calls: method => path.
$endpoints = [
    ['post', '/txns'],
    ['get', '/txns/{txnNumber}'],
    ['post', '/txns/search'],
    ['get', '/txns/resultkeys/{resultKey}'],
    ['post', '/txns/authkeys'],
    ['put', '/txns/authkeys/{authkey}/txn-details'],
    ['get', '/txns/authkeys/{authkey}/payment-method'],
    ['put', '/txns/authkeys/{authkey}/payment-method'],
    ['post', '/txns/authkeys/{authkey}/process'],
    ['post', '/tokens'],
    ['get', '/tokens/{token}'],
    ['put', '/tokens/{token}'],
    ['delete', '/tokens/{token}'],
    ['post', '/tokens/search'],
    ['post', '/tokens/txn/{txnNumber}'],
    ['post', '/tokens/authkeys'],
    ['put', '/tokens/authkeys/{authkey}/token-details'],
    ['post', '/tokens/authkeys/{authkey}/process'],
];

foreach ($endpoints as [$method, $path]) {
    if (! isset($spec['paths'][$path][$method])) {
        $errors[] = 'Endpoint gone: '.strtoupper($method)." {$path}";
    }
}

// Enums: spec definition.property => package enum.
$enums = [
    'TwoPartyTxnProcessRequest.action' => Enums\Action::class,
    'TwoPartyTxnProcessRequest.type' => Enums\TransactionType::class,
    'TwoPartyTxnProcessRequest.subType' => Enums\SubType::class,
    'TxnDetailsRequest.tokenisationMode' => Enums\TokenisationMode::class,
    'MaskedCardDetails.scheme' => Enums\CardScheme::class,
    'Error.code' => Enums\ErrorCode::class,
];

foreach ($enums as $field => $enum) {
    [$definition, $property] = explode('.', $field);
    $specValues = $spec['definitions'][$definition]['properties'][$property]['enum'] ?? null;

    if (! is_array($specValues)) {
        $errors[] = "{$field} is no longer an enum in the spec.";

        continue;
    }

    $ours = array_map(fn ($case) => $case->value, $enum::cases());

    foreach (array_diff($ours, $specValues) as $value) {
        $errors[] = "{$enum}::{$value} is not accepted by {$field} any more.";
    }
    foreach (array_diff($specValues, $ours) as $value) {
        $warnings[] = "{$field} has a new value the package does not model: {$value}";
    }
}

// Required fields: spec definition => what the package builds for it.
$card = new CardDetails('5123456789012346', '05/29', '123');
$details = new TransactionDetails(amount: 100, crn1: 'X', currency: 'AUD');
$bodies = [
    'TwoPartyTxnProcessRequest' => [...$details->toTwoPartyArray(), 'cardDetails' => $card->toArray()],
    'TxnDetailsRequest' => $details->toAuthkeyArray(),
    'TokenDetailsRequest' => (new TokenDetails('X'))->toArray(),
    // card and bank are both listed as required, but the spec's own
    // description says only one of them is sent.
    'TokenAddRequest' => [...(new TokenDetails('X'))->toArray(), 'card' => $card->toArray(withCvn: false), 'bank' => null],
];

foreach ($bodies as $definition => $body) {
    $schema = $spec['definitions'][$definition] ?? null;
    if ($schema === null) {
        $errors[] = "Definition gone: {$definition}";

        continue;
    }

    foreach ((array) ($schema['required'] ?? []) as $required) {
        if (! array_key_exists($required, $body)) {
            $errors[] = "{$definition} now requires '{$required}', which the package does not send.";
        }
    }

    foreach (array_keys($body) as $key) {
        if (! isset($schema['properties'][$key])) {
            $errors[] = "{$definition} no longer has a '{$key}' property.";
        }
    }
}

foreach ($warnings as $warning) {
    echo "warning: {$warning}\n";
}
foreach ($errors as $error) {
    echo "error:   {$error}\n";
}

echo $errors ? "\nSpec drift found.\n" : "Package matches the published spec.\n";

exit($errors ? 1 : 0);
