<?php

namespace Jonston\AmazonAdsApi\Resources;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Jonston\AmazonAdsApi\AmazonClient;

class MarketingStreamResource
{
    protected string $path = '/streams/subscriptions';

    protected AmazonClient $client;
    protected string $profileId;

    public function __construct(AmazonClient $client, string $profileId) {
        $this->client = $client;

        $this->setProfileId($profileId);
    }

    public function setProfileId(string $profileId): void
    {
        $this->profileId = $profileId;

        $this->client->withHeaders([
            'Amazon-Advertising-API-Scope' => $profileId
        ]);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function list(array $params = []): array
    {
        return $this->client->request('GET', $this->path, [
            'query' => $params
        ]);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function create(array $data): array
    {
        return $this->client->request('POST', $this->path, [
            'json' => $data
        ]);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function update(string $id, array $data): array
    {
        return $this->client->request('PUT', "$this->path/{$id}", [
            'json' => $data
        ]);
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function get(string $id): array
    {
        return $this->client->request('GET', "$this->path/{$id}");
    }
}