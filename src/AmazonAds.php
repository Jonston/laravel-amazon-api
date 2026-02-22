<?php

namespace Jonston\AmazonAdsApi;

use Jonston\AmazonAdsApi\Resources\ProfileResource;
use Jonston\AmazonAdsApi\Resources\MarketingStreamResource;

class AmazonAds
{
    protected string $clientId;
    protected string $accessToken;

    public function __construct(string $clientId, string $accessToken)
    {
        $this->authorize($clientId, $accessToken);
    }

    public function authorize(string $clientId, string $accessToken)
    {
        $this->clientId = $clientId;
        $this->accessToken = $accessToken;

        $client = app(AmazonClient::class);

        return $client->authorize($this->clientId, $this->accessToken);
    }

    public function profiles(): ProfileResource
    {
        $client = app(AmazonClient::class);

        return new ProfileResource($client);
    }

    public function marketingStream(string $profileId): MarketingStreamResource
    {
        $client = app(AmazonClient::class);

        return new MarketingStreamResource($client, $profileId);
    }
}