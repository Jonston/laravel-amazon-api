<?php

namespace Jonston\AmazonAdsApi\Resources;

use Illuminate\Http\Client\ConnectionException;
use Jonston\AmazonAdsApi\AmazonClient;
use Jonston\AmazonAdsApi\Contracts\AmazonResourceContract;
use Jonston\AmazonAdsApi\Exceptions\AmazonApiException;

class MarketingStreamResource implements AmazonResourceContract
{
    protected string $path = '/streams/subscriptions';

    private readonly AmazonClient $scopedClient;

    public function __construct(AmazonClient $client, string $profileId)
    {
        // Создаём иммутабельный клон клиента с заголовком scope —
        // оригинальный клиент не мутируется
        $this->scopedClient = $client->withHeaders([
            'Amazon-Advertising-API-Scope' => $profileId,
        ]);
    }

    /**
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function list(array $params = []): array
    {
        return $this->scopedClient->request('GET', $this->path, [
            'query' => $params,
        ]);
    }

    /**
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function get(string $id): array
    {
        return $this->scopedClient->request('GET', "{$this->path}/{$id}");
    }

    /**
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function create(array $data): array
    {
        return $this->scopedClient->request('POST', $this->path, [
            'json' => $data,
        ]);
    }

    /**
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function update(string $id, array $data): array
    {
        return $this->scopedClient->request('PUT', "{$this->path}/{$id}", [
            'json' => $data,
        ]);
    }

    /**
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function delete(string $id): array
    {
        return $this->scopedClient->request('DELETE', "{$this->path}/{$id}");
    }
}