<?php

declare(strict_types=1);

namespace Jonston\AmazonAds\Http;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use Jonston\AmazonAds\Auth\Credentials;
use Jonston\AmazonAds\Auth\TokenResolver;
use Jonston\AmazonAds\Exceptions\AmazonAdsApiException;
use Jonston\AmazonAds\Exceptions\RateLimitException;

final readonly class HttpClient
{
    public function __construct(
        private HttpFactory $http,
        private TokenResolver $tokenResolver,
    ) {}

    public function get(Credentials $credentials, string $path, array $query = [], array $headers = []): array
    {
        return $this->request('GET', $credentials, $path, query: $query, extraHeaders: $headers);
    }

    public function post(Credentials $credentials, string $path, array $body = [], array $headers = []): array
    {
        return $this->request('POST', $credentials, $path, body: $body, extraHeaders: $headers);
    }

    public function put(Credentials $credentials, string $path, array $body = [], array $headers = []): array
    {
        return $this->request('PUT', $credentials, $path, body: $body, extraHeaders: $headers);
    }

    public function delete(Credentials $credentials, string $path, array $headers = []): array
    {
        return $this->request('DELETE', $credentials, $path, extraHeaders: $headers);
    }

    private function request(
        string $method,
        Credentials $credentials,
        string $path,
        array $query = [],
        array $body = [],
        array $extraHeaders = [],
    ): array {
        $accessToken = $this->tokenResolver->resolve($credentials);

        $headers = array_merge([
            'Authorization' => "Bearer {$accessToken->token}",
            'Amazon-Advertising-API-ClientId' => $credentials->clientId,
        ], $extraHeaders);

        $url = rtrim($credentials->endpoint, '/') . '/' . ltrim($path, '/');

        try {
            $pending = $this->http
                ->withHeaders($headers)
                ->timeout(30)
                ->acceptJson();

            $response = match ($method) {
                'GET' => $pending->get($url, $query),
                'POST' => $pending->post($url, $body),
                'PUT' => $pending->put($url, $body),
                'DELETE' => $pending->delete($url),
                default  => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
            };

            if ($response->status() === 429) {
                throw new RateLimitException('Amazon Ads API rate limit exceeded.');
            }

            $response->throw();

            $contents = $response->body();

            if (empty($contents)) {
                return [];
            }

            return $response->json() ?? [];
        } catch (RequestException $e) {
            $laravelResponse = $e->response;
            $status = $laravelResponse->status();
            $body = [];

            try {
                $body = $laravelResponse->json() ?? [];
            } catch (\Throwable) {
                // non-JSON body — ignore
            }

            $message = $body['message'] ?? $body['error_description'] ?? "Amazon Ads API error (HTTP {$status})";

            throw new AmazonAdsApiException($message, $status, $body, $e);
        } catch (\Throwable $e) {
            throw new AmazonAdsApiException($e->getMessage(), 0, [], $e);
        }
    }
}
