<?php

namespace Jonston\AmazonAdsApi\Resources;

use Illuminate\Http\Client\ConnectionException;
use Jonston\AmazonAdsApi\AmazonClient;
use Jonston\AmazonAdsApi\Exceptions\AmazonApiException;

/**
 * Marketing Stream Subscriptions API.
 *
 * Каждый экземпляр ресурса привязан к конкретному profileId (Amazon Advertising Scope).
 * Использует иммутабельный клон AmazonClient — оригинальный клиент не мутируется.
 */
class MarketingStreamSubscriptionResource
{
    private const PATH = '/streams/subscriptions';

    private readonly AmazonClient $client;

    public function __construct(AmazonClient $client, string $profileId)
    {
        $this->client = $client->withHeaders([
            'Amazon-Advertising-API-Scope' => $profileId,
        ]);
    }

    /**
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function list(array $params = []): array
    {
        return $this->client->request('GET', self::PATH, ['query' => $params]);
    }

    /**
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function get(string $id): array
    {
        return $this->client->request('GET', self::PATH . "/{$id}");
    }

    /**
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function create(array $data): array
    {
        return $this->client->request('POST', self::PATH, ['json' => $data]);
    }

    /**
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function update(string $id, array $data): array
    {
        return $this->client->request('PUT', self::PATH . "/{$id}", ['json' => $data]);
    }

    /**
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function delete(string $id): array
    {
        return $this->client->request('DELETE', self::PATH . "/{$id}");
    }
}