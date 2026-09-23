<?php

namespace DK\MerchantSuite\Http;

use DK\MerchantSuite\Exceptions\ApiException;
use DK\MerchantSuite\Exceptions\ConfigurationException;
use DK\MerchantSuite\Exceptions\ConnectionException;
use DK\MerchantSuite\Exceptions\UnexpectedResponseException;
use DK\MerchantSuite\Support\Payload;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Http\Client\ConnectionException as HttpConnectionException;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Throwable;

/**
 * Thin wrapper over Laravel's HTTP client.
 *
 * Request and response bodies are never logged or put into exception
 * messages, because they can carry card numbers.
 */
class Client
{
    private readonly Payload $config;

    /**
     * @param  array<array-key, mixed>  $config  the merchantsuite config array
     */
    public function __construct(
        private readonly Factory $http,
        array $config,
    ) {
        $this->config = Payload::of($config);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function get(string $path): array
    {
        return $this->send('GET', $path);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<array-key, mixed>
     */
    public function post(string $path, array $body = []): array
    {
        return $this->send('POST', $path, $body);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<array-key, mixed>
     */
    public function put(string $path, array $body = []): array
    {
        return $this->send('PUT', $path, $body);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function delete(string $path): array
    {
        return $this->send('DELETE', $path);
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @return array<array-key, mixed>
     */
    private function send(string $method, string $path, ?array $body = null): array
    {
        $request = $this->request();

        // Only GET is safe to repeat. Retrying a POST /txns after a timeout
        // can charge the card twice. retry() counts attempts, not retries.
        if ($method === 'GET' && ($retries = $this->config->int('get_retries') ?? 0) > 0) {
            $request->retry($retries + 1, 250, fn (Throwable $e) => self::retryable($e), throw: false);
        }

        try {
            /** @var Response $response */
            $response = $request->send($method, ltrim($path, '/'), $body === null ? [] : ['json' => (object) $body]);
        } catch (HttpConnectionException|TransferException $e) {
            // Older Laravel 12 releases only wrap Guzzle's ConnectException,
            // so a connection dropped mid-request can arrive as a raw
            // Guzzle TransferException.
            throw new ConnectionException(
                "Could not reach MerchantSuite ({$method} {$path}).".($method === 'GET' ? '' : ' The outcome is unknown; look the transaction up before retrying.'),
                $method,
                $path,
                $e,
            );
        }

        $json = $response->json();

        if ($response->failed()) {
            throw ApiException::fromResponse($response->status(), is_array($json) ? $json : null);
        }

        // An empty body is normal (201 on attach calls, DELETE). Anything
        // else that is not JSON is a proxy or maintenance page, not an answer.
        if ($json === null && ! in_array(trim($response->body()), ['', 'null'], true)) {
            throw new UnexpectedResponseException(
                "MerchantSuite returned HTTP {$response->status()} with a body that is not JSON ({$method} {$path}).",
                $response->status(),
            );
        }

        return is_array($json) ? $json : [];
    }

    private static function retryable(Throwable $e): bool
    {
        return $e instanceof HttpConnectionException
            || ($e instanceof RequestException && in_array($e->response->status(), [502, 503, 504], true));
    }

    private function request(): PendingRequest
    {
        $value = function (string $key): string {
            return $this->config->str($key) ?? throw ConfigurationException::missing($key);
        };

        return $this->http
            ->baseUrl(rtrim($value('base_url'), '/').'/')
            ->withBasicAuth($value('username').'|'.$value('merchant_number'), $value('password'))
            ->acceptJson()
            ->asJson()
            ->timeout($this->config->int('timeout') ?? 65)
            ->connectTimeout($this->config->int('connect_timeout') ?? 10)
            ->withUserAgent('dipinkrishna/laravel-merchantsuite');
    }
}
