<?php

namespace Jonston\AmazonAdsApi\Resources;

use Illuminate\Http\Client\ConnectionException;
use Jonston\AmazonAdsApi\AmazonClient;
use Jonston\AmazonAdsApi\Exceptions\AmazonApiException;

/**
 * Provides access to the Amazon Ads Profiles API (/v2/profiles).
 */
class ProfileResource
{
    private const PATH = '/v2/profiles';

    public function __construct(private readonly AmazonClient $client) {}

    /**
     * Return all profiles available to the authorized user.
     *
     * @return array
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function list(): array
    {
        return $this->client->request('GET', self::PATH);
    }

    /**
     * Return a single profile by ID.
     *
     * @param string $profileId
     * @return array
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function get(string $profileId): array
    {
        return $this->client->request('GET', self::PATH . "/{$profileId}");
    }
}