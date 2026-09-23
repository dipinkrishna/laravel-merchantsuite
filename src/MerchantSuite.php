<?php

namespace DK\MerchantSuite;

use DK\MerchantSuite\Data\Expiry;
use DK\MerchantSuite\Data\Transaction;
use DK\MerchantSuite\Data\TransactionDetails;
use DK\MerchantSuite\Http\Client;
use DK\MerchantSuite\Resources\Checkout;
use DK\MerchantSuite\Resources\Tokens;
use DK\MerchantSuite\Resources\Transactions;
use DK\MerchantSuite\Support\Payload;
use DK\MerchantSuite\Webhooks\Webhooks;

class MerchantSuite
{
    private ?Transactions $transactions = null;

    private ?Tokens $tokens = null;

    private ?Checkout $checkout = null;

    /**
     * @param  array<string, mixed>  $config  the merchantsuite config array
     */
    public function __construct(
        private readonly Client $client,
        private readonly array $config = [],
    ) {}

    public function transactions(): Transactions
    {
        return $this->transactions ??= new Transactions($this->client, $this->defaults());
    }

    public function tokens(): Tokens
    {
        return $this->tokens ??= new Tokens($this->client, $this->defaults());
    }

    public function checkout(): Checkout
    {
        return $this->checkout ??= new Checkout($this->client, $this->defaults());
    }

    public function webhooks(): Webhooks
    {
        /** @var array{verify_ip?: bool, allowed_ips?: list<string>} $config */
        $config = (array) ($this->config['webhooks'] ?? []);

        return new Webhooks($this->transactions(), $this->tokens(), $config);
    }

    /**
     * Charge a stored token from the server, with no card data involved.
     * Runs the AuthKey flow in one go: create, attach details, attach
     * token, process.
     */
    public function chargeToken(string $token, TransactionDetails $details, ?Expiry $expiry = null, ?string $webhookUrl = null): Transaction
    {
        $checkout = $this->checkout();

        $authkey = $checkout->create($details);
        $checkout->attachToken($authkey, $token, $expiry);

        return $checkout->process($authkey, $webhookUrl);
    }

    /**
     * @return array{currency?: string|null, biller_code?: string|null, test_mode?: bool}
     */
    private function defaults(): array
    {
        return [
            'currency' => Payload::of($this->config)->str('currency'),
            'biller_code' => Payload::of($this->config)->str('biller_code'),
            'test_mode' => (bool) ($this->config['test_mode'] ?? true),
        ];
    }
}
