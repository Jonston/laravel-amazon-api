<?php

namespace Jonston\AmazonAdsApi\Resources;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Jonston\AmazonAdsApi\AmazonClient;

class ProfileResource
{
    public function __construct(protected AmazonClient $client) {}

    /**
     * Get a list of all profiles that the authorized user has access to.
     *
     * @throws RequestException
     * @throws ConnectionException
     */
    public function list(): array
    {
        return $this->client->request('GET', '/v2/profiles');
    }
}