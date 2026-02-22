<?php

namespace Jonston\AmazonAdsApi;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\Exceptions\AmazonApiException;

/**
 * HTTP-транспорт для Amazon Ads API.
 *
 * Отвечает за:
 * - получение и кэширование access_token
 * - формирование заголовков
 * - выполнение HTTP-запросов
 *
 * Иммутабелен: withHeaders() возвращает новый экземпляр.
 */
final class AmazonClient
{
    private array $extraHeaders = [];

    public function __construct(
        private readonly AmazonCredentials $credentials,
    ) {}

    /**
     * Вернуть новый экземпляр клиента с дополнительными заголовками.
     * Оригинальный клиент не изменяется.
     */
    public function withHeaders(array $headers): self
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
     * Получить OAuthClient для этих credentials (например, для exchangeCode).
     */
    public function oauth(): OAuthClient
    {
        return new OAuthClient($this->credentials);
    }

    // ---

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

        return Cache::remember($key, now()->addMinutes(55), fn () => (new OAuthClient($this->credentials))->refreshAccessToken());
    }
}