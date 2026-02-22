<?php

namespace Jonston\AmazonAdsApi;

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
 *   $amazon->authorize($credentialsA)->profiles()->list();
 *   $amazon->authorize($credentialsB)->marketingStreamSubscriptions($profileId)->create([...]);
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
     * Return the Marketing Stream subscriptions resource for a given profile.
     *
     * @param string $profileId Amazon Advertising API Scope identifier
     * @return MarketingStreamSubscriptionResource
     */
    public function marketingStreamSubscriptions(string $profileId): MarketingStreamSubscriptionResource
    {
        return new MarketingStreamSubscriptionResource($this->resolveClient(), $profileId);
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