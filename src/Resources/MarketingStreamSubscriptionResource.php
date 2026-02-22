<?php

namespace Jonston\AmazonAdsApi\Resources;

use Illuminate\Http\Client\ConnectionException;
use Jonston\AmazonAdsApi\AmazonClient;
use Jonston\AmazonAdsApi\Exceptions\AmazonApiException;

/**
 * Provides access to the Amazon Marketing Stream Subscriptions API (/streams/subscriptions).
 *
 * Each instance is scoped to a single profileId via the Amazon-Advertising-API-Scope header.
 * The underlying client is cloned on construction — the original is never mutated.
 */
class MarketingStreamSubscriptionResource
{
    private const PATH = '/streams/subscriptions';

    private readonly AmazonClient $client;

    /**
     * @param AmazonClient $client
     * @param string       $profileId Amazon Advertising API Scope identifier
     */
    public function __construct(AmazonClient $client, string $profileId)
    {
        $this->client = $client->withHeaders([
            'Amazon-Advertising-API-Scope' => $profileId,
        ]);
    }

    /**
     * Return a list of subscriptions, optionally filtered by query parameters.
     *
     * @param array $params
     * @return array
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function list(array $params = []): array
    {
        return $this->client->request('GET', self::PATH, ['query' => $params]);
    }

    /**
     * Return a single subscription by ID.
     *
     * @param string $id
     * @return array
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function get(string $id): array
    {
        return $this->client->request('GET', self::PATH . "/{$id}");
    }

    /**
     * Create a new subscription.
     *
     * @param array $data
     * @return array
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function create(array $data): array
    {
        return $this->client->request('POST', self::PATH, ['json' => $data]);
    }

    /**
     * Update an existing subscription.
     *
     * @param string $id
     * @param array  $data
     * @return array
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function update(string $id, array $data): array
    {
        return $this->client->request('PUT', self::PATH . "/{$id}", ['json' => $data]);
    }

    /**
     * Delete a subscription by ID.
     *
     * @param string $id
     * @return array
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function delete(string $id): array
    {
        return $this->client->request('DELETE', self::PATH . "/{$id}");
    }
}