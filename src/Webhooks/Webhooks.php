<?php

namespace DK\MerchantSuite\Webhooks;

use DK\MerchantSuite\Data\Token;
use DK\MerchantSuite\Data\Transaction;
use DK\MerchantSuite\Exceptions\InvalidWebhookException;
use DK\MerchantSuite\Exceptions\NotFoundException;
use DK\MerchantSuite\Resources\Tokens;
use DK\MerchantSuite\Resources\Transactions;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * MerchantSuite webhooks carry no signature, so anyone who finds the URL
 * can post to it. verify() never trusts the body: it takes the txnNumber or
 * token from it and fetches the real record from the API.
 */
class Webhooks
{
    /**
     * @param  array{verify_ip?: bool, allowed_ips?: list<string>}  $config
     */
    public function __construct(
        private readonly Transactions $transactions,
        private readonly Tokens $tokens,
        private readonly array $config = [],
    ) {}

    /**
     * Parse and confirm a webhook against the API. Use this one.
     */
    public function verify(Request $request): WebhookEvent
    {
        $this->checkIp($request);

        $event = $this->parse($request);

        try {
            $data = $event->data instanceof Transaction
                ? $this->transactions->find((string) $event->data->txnNumber)
                : $this->tokens->find($event->data->token);
        } catch (NotFoundException) {
            // A made-up txnNumber or token: the request did not come from
            // MerchantSuite. Answer 400 rather than 500, which the sender
            // would retry for 24 hours.
            throw new InvalidWebhookException('Webhook refers to a record MerchantSuite does not have.');
        }

        return new WebhookEvent($event->type, $data, verified: true);
    }

    /**
     * Read the payload as sent, without calling the API. Only for logging or
     * tests: the content is unauthenticated.
     */
    public function parse(Request $request): WebhookEvent
    {
        $type = $request->input('type');
        $data = $request->input('data');

        if (! is_array($data)) {
            throw new InvalidWebhookException('Webhook body has no data object.');
        }

        return match ($type) {
            'transaction' => filled($data['txnNumber'] ?? null)
                ? new WebhookEvent('transaction', Transaction::fromArray($data), verified: false)
                : throw new InvalidWebhookException('Transaction webhook has no txnNumber.'),
            'token' => filled($data['token'] ?? null) && is_string($data['token'])
                ? new WebhookEvent('token', Token::fromArray($data), verified: false)
                : throw new InvalidWebhookException('Token webhook has no token.'),
            default => throw new InvalidWebhookException('Unknown webhook type.'),
        };
    }

    private function checkIp(Request $request): void
    {
        if (! ($this->config['verify_ip'] ?? false)) {
            return;
        }

        $ip = (string) $request->ip();
        if (! IpUtils::checkIp($ip, $this->config['allowed_ips'] ?? [])) {
            throw new InvalidWebhookException("Webhook came from {$ip}, which is not a MerchantSuite address.");
        }
    }
}
