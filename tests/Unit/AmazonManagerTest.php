<?php

namespace Jonston\AmazonAdsApi\Tests\Unit;

use Jonston\AmazonAdsApi\AmazonAds;
use Jonston\AmazonAdsApi\AmazonManager;
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\Enums\RegionEnum;
use Jonston\AmazonAdsApi\Exceptions\AmazonApiException;
use Jonston\AmazonAdsApi\Tests\TestCase;

class AmazonManagerTest extends TestCase
{
    public function test_resolves_default_account(): void
    {
        $manager = app(AmazonManager::class);

        $this->assertInstanceOf(AmazonAds::class, $manager->account('default'));
    }

    public function test_throws_exception_for_unknown_account(): void
    {
        $manager = app(AmazonManager::class);

        $this->expectException(AmazonApiException::class);
        $this->expectExceptionMessage('Amazon Ads account [unknown] is not configured.');

        $manager->account('unknown');
    }

    public function test_dynamic_account_registration(): void
    {
        $manager = app(AmazonManager::class);

        $credentials = new AmazonCredentials(
            clientId:     'dynamic-client-id',
            clientSecret: 'dynamic-secret',
            refreshToken: 'dynamic-refresh',
            region:       RegionEnum::EU,
        );

        $manager->addAccount('agency-eu', $credentials);

        $this->assertTrue($manager->hasAccount('agency-eu'));
        $this->assertInstanceOf(AmazonAds::class, $manager->account('agency-eu'));
    }

    public function test_account_names_returns_all_registered(): void
    {
        $manager = app(AmazonManager::class);

        $manager->addAccount('client-a', new AmazonCredentials('id', 'secret', 'token', RegionEnum::NA));
        $manager->addAccount('client-b', new AmazonCredentials('id', 'secret', 'token', RegionEnum::FE));

        $names = $manager->accountNames();

        $this->assertContains('default',  $names);
        $this->assertContains('client-a', $names);
        $this->assertContains('client-b', $names);
    }

    public function test_account_from_array_credentials(): void
    {
        $credentials = AmazonCredentials::fromArray([
            'client_id'     => 'id',
            'client_secret' => 'secret',
            'refresh_token' => 'token',
            'region'        => 'eu',
        ]);

        $this->assertSame(RegionEnum::EU, $credentials->region);
        $this->assertSame('id', $credentials->clientId);
    }
}

