<?php

namespace Jonston\AmazonAdsApi;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\Exceptions\AmazonApiException;

class AmazonClient
{
    private readonly OAuthClient $oauthClient;

    public function __construct(
        private readonly AmazonCredentials $credentials,
        private readonly bool $sandbox = false,
    ) {
        $this->oauthClient = new OAuthClient($credentials);
    }

    /**
     * Получить access_token (с кэшированием на 55 минут).
     */
    public function getAccessToken(): string
    {
        $cacheKey = 'amazon_ads_token_' . md5($this->credentials->clientId . $this->credentials->refreshToken);

        return Cache::remember($cacheKey, now()->addMinutes(55), function () {
            return $this->oauthClient->getAccessToken();
        });
    }

    /**
     * Создать новый экземпляр клиента с дополнительными заголовками (иммутабельно).
     */
    public function withHeaders(array $headers): static
    {
        $clone = clone $this;
        $clone->extraHeaders = array_merge($this->extraHeaders, $headers);

        return $clone;
    }

    /**
     * Выполнить HTTP-запрос к Amazon Ads API.
     *
     * @throws ConnectionException
     * @throws AmazonApiException
     */
    public function request(string $method, string $path, array $options = []): array
    {
        $baseUrl = $this->credentials->region->getBaseUrl($this->sandbox);
        $url     = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');

        try {
            $response = Http::withHeaders($this->buildHeaders())
                ->send($method, $url, $options);

            if ($response->failed()) {
                throw AmazonApiException::requestFailed(
                    "HTTP {$response->status()}: {$response->body()}"
                );
            }

            return $response->json() ?? [];
        } catch (ConnectionException $e) {
            throw $e;
        }
    }

    /**
     * Вернуть OAuthClient для данного аккаунта (например, для обмена кода).
     */
    public function oauth(): OAuthClient
    {
        return $this->oauthClient;
    }

    public function getCredentials(): AmazonCredentials
    {
        return $this->credentials;
    }

    // ---

    private array $extraHeaders = [];

    private function buildHeaders(): array
    {
        return array_merge(
            [
                'Authorization'                        => 'Bearer ' . $this->getAccessToken(),
                'Amazon-Advertising-API-ClientId'      => $this->credentials->clientId,
                'Content-Type'                         => 'application/json',
            ],
            $this->extraHeaders,
        );
    }
}