<?php

namespace Jonston\AmazonAdsApi\Tests\Unit;

use Jonston\AmazonAdsApi\AmazonAds;
use Jonston\AmazonAdsApi\AmazonClient;
use Jonston\AmazonAdsApi\DTO\AdvertisingProfile;
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\Enums\RegionEnum;
use Jonston\AmazonAdsApi\Resources\MarketingStreamSubscriptionResource;
use Jonston\AmazonAdsApi\Resources\ProfileResource;
use Jonston\AmazonAdsApi\Tests\TestCase;

class AmazonAdsTest extends TestCase
{
    private function makeCredentials(RegionEnum $region = RegionEnum::NA): AmazonCredentials
    {
        return AmazonCredentials::fromRegion(
            region: $region,
            clientId: 'client-id',
            clientSecret: 'client-secret',
            refreshToken: 'refresh-token',
        );
    }

    private function makeProfile(string $profileId, RegionEnum $region = RegionEnum::NA): AdvertisingProfile
    {
        return new AdvertisingProfile($profileId, $region);
    }

    public function test_authorize_returns_same_instance(): void
    {
        $amazon = app(AmazonAds::class);

        $result = $amazon->authorize($this->makeCredentials());

        $this->assertSame($amazon, $result);
    }

    public function test_throws_when_profiles_called_without_authorize(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No credentials set');

        (new AmazonAds())->profiles();
    }

    public function test_throws_when_marketing_stream_called_without_authorize(): void
    {
        $this->expectException(\LogicException::class);

        (new AmazonAds())->marketingStreamSubscriptions($this->makeProfile('profile-123'));
    }

    public function test_profiles_returns_profile_resource(): void
    {
        $amazon = app(AmazonAds::class)->authorize($this->makeCredentials());

        $this->assertInstanceOf(ProfileResource::class, $amazon->profiles());
    }

    public function test_marketing_stream_subscriptions_returns_correct_resource(): void
    {
        $amazon = app(AmazonAds::class)->authorize($this->makeCredentials());

        $this->assertInstanceOf(
            MarketingStreamSubscriptionResource::class,
            $amazon->marketingStreamSubscriptions($this->makeProfile('profile-123'))
        );
    }

    public function test_client_returns_amazon_client(): void
    {
        $amazon = app(AmazonAds::class)->authorize($this->makeCredentials());

        $this->assertInstanceOf(AmazonClient::class, $amazon->client());
    }

    public function test_authorize_switches_credentials(): void
    {
        $amazon = app(AmazonAds::class);

        $credA = $this->makeCredentials(RegionEnum::NA);
        $credB = $this->makeCredentials(RegionEnum::EU);

        $amazon->authorize($credA);
        $this->assertStringContainsString('advertising-api.amazon.com', $credA->baseUrl);

        $amazon->authorize($credB);
        $this->assertStringContainsString('advertising-api-eu.amazon.com', $credB->baseUrl);
    }

    public function test_marketing_stream_uses_profile_region_endpoint(): void
    {
        // Agency credentials are NA, but we work with an EU profile
        $amazon = app(AmazonAds::class)->authorize($this->makeCredentials(RegionEnum::NA));

        $euProfile = $this->makeProfile('profile-eu', RegionEnum::EU);
        $naProfile = $this->makeProfile('profile-na', RegionEnum::NA);

        // Each resource uses its profile's region — no cross-region leakage
        $this->assertInstanceOf(MarketingStreamSubscriptionResource::class,
            $amazon->marketingStreamSubscriptions($euProfile));
        $this->assertInstanceOf(MarketingStreamSubscriptionResource::class,
            $amazon->marketingStreamSubscriptions($naProfile));
    }

    public function test_multiple_marketing_stream_resources_do_not_share_state(): void
    {
        $amazon = app(AmazonAds::class)->authorize($this->makeCredentials());

        $resourceA = $amazon->marketingStreamSubscriptions($this->makeProfile('profile-aaa'));
        $resourceB = $amazon->marketingStreamSubscriptions($this->makeProfile('profile-bbb'));

        $this->assertNotSame($resourceA, $resourceB);
    }
}
