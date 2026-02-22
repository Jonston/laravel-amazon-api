<?php

namespace Jonston\AmazonAdsApi;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Jonston\AmazonAdsApi\Enums\RegionEnum;

class AmazonClient
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $accessToken;
    protected array $headers = [];

    public function __construct(
        string $clientId,
        string $accessToken,
        string $baseUrl,
    ) {
        $this->authorize($clientId, $accessToken)
            ->setBaseUrl($baseUrl);
    }

    public function authorize(string $clientId, string $accessToken): self
    {
        $this->clientId = $clientId;
        $this->accessToken = $accessToken;

        return $this;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    /**
     * Выполнение запроса через Laravel Http Facade
     * @throws ConnectionException
     * @throws RequestException
     */
    public function request(string $method, string $path, array $options = []): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');

        $response = Http::withHeaders($this->getHeaders())
            ->send($method, $url, $options);

        $response->throw();

        return $response->json() ?? [];
    }

    public function withHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    protected function getHeaders(): array
    {
        $headers = [
            'Authorization' => "Bearer {$this->accessToken}",
            'Amazon-Advertising-API-ClientId' => $this->clientId,
        ];

        return array_merge($headers, $this->headers);
    }
}