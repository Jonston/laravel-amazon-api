<?php

declare(strict_types=1);

namespace Jonston\AmazonAds;

use Jonston\AmazonAds\Auth\Credentials;
use Jonston\AmazonAds\Contracts\Resource;
use Jonston\AmazonAds\Http\HttpClient;
use Jonston\AmazonAds\Resources\Campaigns;
use Jonston\AmazonAds\Resources\MarketingStreamSubscriptions;
use Jonston\AmazonAds\Resources\Profiles;
use Jonston\AmazonAds\Support\ResourceFactory;

final class AuthorizedClient
{
    private readonly ResourceFactory $factory;

    public function __construct(
        private readonly HttpClient $httpClient,
        private readonly Credentials $credentials,
    ) {
        $this->factory = new ResourceFactory($httpClient, $credentials);
    }

    public function profiles(): Profiles
    {
        return new Profiles($this->httpClient, $this->credentials);
    }

    public function marketingStreamSubscriptions(string $profileId): MarketingStreamSubscriptions
    {
        return new MarketingStreamSubscriptions($this->httpClient, $this->credentials, $profileId);
    }

    public function campaigns(string $profileId): Campaigns
    {
        return new Campaigns($this->httpClient, $this->credentials, $profileId);
    }

    /**
     * Escape hatch для кастомных ресурсов без profileId.
     *
     * @template T of Resource
     * @param class-string<T> $resourceClass
     * @return T
     */
    public function resource(string $resourceClass): Resource
    {
        return $this->factory->make($resourceClass);
    }
}
