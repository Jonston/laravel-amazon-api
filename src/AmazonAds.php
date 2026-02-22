<?php

namespace Jonston\AmazonAdsApi;

use Jonston\AmazonAdsApi\DTO\AdvertisingProfile;
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\Resources\MarketingStreamSubscriptionResource;
use Jonston\AmazonAdsApi\Resources\ProfileResource;

/**
 * Entry point for the Amazon Ads API.
 *
 * Registered as a singleton in the Laravel container.
 * Switch between accounts by calling authorize() with different credentials.
 *
 * @example
 *   $amazon->authorize($agencyCredentials)->profiles()->list();
 *   $amazon->authorize($agencyCredentials)->marketingStreamSubscriptions($profileEU)->create($data);
 *   $amazon->authorize($agencyCredentials)->marketingStreamSubscriptions($profileNA)->create($data);
 */
class AmazonAds
{
    private ?AmazonClient $client = null;

    /**
     * Set credentials for the current request context.
     *
     * @param AmazonCredentials $credentials
     * @return static
     */
    public function authorize(AmazonCredentials $credentials): static
    {
        $this->client = new AmazonClient($credentials);

        return $this;
    }

    /**
     * Return the profiles resource.
     *
     * @return ProfileResource
     */
    public function profiles(): ProfileResource
    {
        return new ProfileResource($this->resolveClient());
    }

    /**
     * Return the Marketing Stream subscriptions resource scoped to the given profile.
     *
     * The client endpoint is automatically switched to match the profile's region,
     * so EU and NA profiles can be used within the same agency session.
     *
     * @param AdvertisingProfile $profile
     * @return MarketingStreamSubscriptionResource
     */
    public function marketingStreamSubscriptions(AdvertisingProfile $profile): MarketingStreamSubscriptionResource
    {
        $client = $this->resolveClient()->withBaseUrl($profile->region->baseUrl());

        return new MarketingStreamSubscriptionResource($client, $profile->profileId);
    }

    /**
     * Return the underlying HTTP client for custom requests.
     *
     * @return AmazonClient
     */
    public function client(): AmazonClient
    {
        return $this->resolveClient();
    }

    private function resolveClient(): AmazonClient
    {
        if ($this->client === null) {
            throw new \LogicException(
                'No credentials set. Call ->authorize(AmazonCredentials $credentials) first.'
            );
        }

        return $this->client;
    }
}