<?php

namespace Jonston\AmazonAdsApi\Tests\Unit;

use Jonston\AmazonAdsApi\DTO\AdvertisingProfile;
use Jonston\AmazonAdsApi\DTO\CreateSubscriptionData;
use Jonston\AmazonAdsApi\DTO\UpdateSubscriptionData;
use Jonston\AmazonAdsApi\Enums\RegionEnum;
use Jonston\AmazonAdsApi\Tests\TestCase;

class DtoTest extends TestCase
{
    public function test_advertising_profile_stores_id_and_region(): void
    {
        $profile = new AdvertisingProfile('profile-123', RegionEnum::EU);

        $this->assertSame('profile-123', $profile->profileId);
        $this->assertSame(RegionEnum::EU, $profile->region);
    }

    public function test_advertising_profile_from_array_with_region(): void
    {
        $profile = AdvertisingProfile::fromArray([
            'profileId' => '123456',
            'region' => 'EU',
        ]);

        $this->assertSame('123456', $profile->profileId);
        $this->assertSame(RegionEnum::EU, $profile->region);
    }

    public function test_advertising_profile_from_array_with_country_code(): void
    {
        $profile = AdvertisingProfile::fromArray([
            'profileId' => '789',
            'countryCode' => 'US',
        ]);

        $this->assertSame(RegionEnum::NA, $profile->region);
    }

    public function test_region_from_country_code_na(): void
    {
        $this->assertSame(RegionEnum::NA, RegionEnum::fromCountryCode('US'));
        $this->assertSame(RegionEnum::NA, RegionEnum::fromCountryCode('CA'));
        $this->assertSame(RegionEnum::NA, RegionEnum::fromCountryCode('MX'));
        $this->assertSame(RegionEnum::NA, RegionEnum::fromCountryCode('BR'));
    }

    public function test_region_from_country_code_fe(): void
    {
        $this->assertSame(RegionEnum::FE, RegionEnum::fromCountryCode('JP'));
        $this->assertSame(RegionEnum::FE, RegionEnum::fromCountryCode('AU'));
        $this->assertSame(RegionEnum::FE, RegionEnum::fromCountryCode('SG'));
    }

    public function test_region_from_country_code_eu_is_default(): void
    {
        $this->assertSame(RegionEnum::EU, RegionEnum::fromCountryCode('GB'));
        $this->assertSame(RegionEnum::EU, RegionEnum::fromCountryCode('DE'));
        $this->assertSame(RegionEnum::EU, RegionEnum::fromCountryCode('FR'));
    }

    // --- CreateSubscriptionData ---

    public function test_create_subscription_serializes_required_fields(): void
    {
        $data = new CreateSubscriptionData(
            dataSetId: 'SPONSORED_PRODUCTS_CAMPAIGN_DIAGNOSTICS',
            destinationType: 'SQS',
            destinationArn: 'arn:aws:sqs:eu-west-1:123456789:my-queue',
        );

        $payload = $data->toArray();

        $this->assertSame('SPONSORED_PRODUCTS_CAMPAIGN_DIAGNOSTICS', $payload['dataSetId']);
        $this->assertSame('SQS', $payload['destinationType']);
        $this->assertSame('arn:aws:sqs:eu-west-1:123456789:my-queue', $payload['destinationArn']);
        $this->assertArrayNotHasKey('notes', $payload);
    }

    public function test_create_subscription_includes_optional_notes(): void
    {
        $data = new CreateSubscriptionData(
            dataSetId: 'ds-1',
            destinationType: 'SQS',
            destinationArn: 'arn:aws:sqs:us-east-1:111:queue',
            notes: 'Agency client subscription',
        );

        $this->assertSame('Agency client subscription', $data->toArray()['notes']);
    }

    // --- UpdateSubscriptionData ---

    public function test_update_subscription_omits_null_fields(): void
    {
        $data = new UpdateSubscriptionData(destinationArn: 'arn:aws:sqs:us-east-1:111:new-queue');

        $payload = $data->toArray();

        $this->assertSame('arn:aws:sqs:us-east-1:111:new-queue', $payload['destinationArn']);
        $this->assertArrayNotHasKey('notes', $payload);
    }

    public function test_update_subscription_empty_when_all_null(): void
    {
        $data = new UpdateSubscriptionData();

        $this->assertSame([], $data->toArray());
    }

    public function test_update_subscription_with_only_notes(): void
    {
        $data = new UpdateSubscriptionData(notes: 'Updated note');

        $payload = $data->toArray();

        $this->assertArrayHasKey('notes', $payload);
        $this->assertArrayNotHasKey('destinationArn', $payload);
    }
}

