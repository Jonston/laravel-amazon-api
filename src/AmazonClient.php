<?php

namespace Jonston\AmazonAdsApi;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\Exceptions\AmazonApiException;

/**
 * HTTP transport layer for the Amazon Ads API.
 *
 * Handles access token resolution, request headers and HTTP execution.
 * Immutable: withHeaders() returns a new instance without modifying the original.
 */
final class AmazonClient
{
    private array $extraHeaders = [];

    public function __construct(
        private readonly AmazonCredentials $credentials,
    ) {}

    /**
     * Return a new instance with additional headers merged in.
     *
     * @param array<string, string> $headers
     * @return self
     */
    public function withHeaders(array $headers): self
    {
        $clone = clone $this;
        $clone->extraHeaders = array_merge($this->extraHeaders, $headers);

        return $clone;
    }

    /**
     * Send an HTTP request to the Amazon Ads API.
     *
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $path   API path (e.g. /v2/profiles)
     * @param array  $options Guzzle-compatible options (query, json, etc.)
     * @return array
     * @throws ConnectionException
     * @throws AmazonApiException
     */
    public function request(string $method, string $path, array $options = []): array
    {
        $url = rtrim($this->credentials->baseUrl, '/') . '/' . ltrim($path, '/');

        $response = Http::withHeaders($this->buildHeaders())->send($method, $url, $options);

        if ($response->failed()) {
            throw AmazonApiException::requestFailed(
                "HTTP {$response->status()}: {$response->body()}"
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Return an OAuthClient bound to the current credentials.
     *
     * @return OAuthClient
     */
    public function oauth(): OAuthClient
    {
        return new OAuthClient($this->credentials);
    }

    private function buildHeaders(): array
    {
        return array_merge([
            'Authorization' => 'Bearer ' . $this->resolveAccessToken(),
            'Amazon-Advertising-API-ClientId' => $this->credentials->clientId,
            'Content-Type' => 'application/json',
        ], $this->extraHeaders);
    }

    private function resolveAccessToken(): string
    {
        $key = 'amazon_ads_token_' . md5($this->credentials->clientId . $this->credentials->refreshToken);

        return Cache::remember(
            $key,
            now()->addMinutes(55),
            fn () => (new OAuthClient($this->credentials))->refreshAccessToken()
        );
    }
}