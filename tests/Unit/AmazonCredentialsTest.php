<?php

namespace Jonston\AmazonAdsApi\Tests\Unit;

use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\Enums\RegionEnum;
use Jonston\AmazonAdsApi\Tests\TestCase;

class AmazonCredentialsTest extends TestCase
{
    public function test_from_region_builds_correct_base_url(): void
    {
        $cred = AmazonCredentials::fromRegion(
            region:       RegionEnum::EU,
            clientId:     'id',
            clientSecret: 'secret',
            refreshToken: 'token',
        );

        $this->assertSame('https://advertising-api-eu.amazon.com', $cred->baseUrl);
        $this->assertSame(AmazonCredentials::TOKEN_ENDPOINT, $cred->tokenEndpoint);
    }

    public function test_from_region_sandbox_uses_sandbox_url(): void
    {
        $cred = AmazonCredentials::fromRegion(
            region:       RegionEnum::NA,
            clientId:     'id',
            clientSecret: 'secret',
            refreshToken: 'token',
            sandbox:      true,
        );

        $this->assertSame('https://advertising-api-test.amazon.com', $cred->baseUrl);
    }

    public function test_from_array_with_region(): void
    {
        $cred = AmazonCredentials::fromArray([
            'client_id'     => 'id',
            'client_secret' => 'secret',
            'refresh_token' => 'token',
            'region'        => 'fe',
        ]);

        $this->assertSame('https://advertising-api-fe.amazon.com', $cred->baseUrl);
        $this->assertSame('id', $cred->clientId);
    }

    public function test_from_array_with_explicit_base_url(): void
    {
        $cred = AmazonCredentials::fromArray([
            'client_id'     => 'id',
            'client_secret' => 'secret',
            'refresh_token' => 'token',
            'base_url'      => 'https://my-custom-endpoint.example.com',
        ]);

        $this->assertSame('https://my-custom-endpoint.example.com', $cred->baseUrl);
    }

    public function test_from_array_with_custom_token_endpoint(): void
    {
        $cred = AmazonCredentials::fromArray([
            'client_id'      => 'id',
            'client_secret'  => 'secret',
            'refresh_token'  => 'token',
            'base_url'       => 'https://some.endpoint.com',
            'token_endpoint' => 'https://custom.oauth.com/token',
        ]);

        $this->assertSame('https://custom.oauth.com/token', $cred->tokenEndpoint);
    }

    public function test_direct_constructor_with_all_params(): void
    {
        $cred = new AmazonCredentials(
            clientId:      'my-client',
            clientSecret:  'my-secret',
            refreshToken:  'my-token',
            baseUrl:       'https://advertising-api.amazon.com',
            tokenEndpoint: 'https://api.amazon.com/auth/o2/token',
        );

        $this->assertSame('my-client', $cred->clientId);
        $this->assertSame('https://advertising-api.amazon.com', $cred->baseUrl);
    }

    public function test_region_enum_all_base_urls(): void
    {
        $this->assertSame('https://advertising-api.amazon.com',    RegionEnum::NA->baseUrl());
        $this->assertSame('https://advertising-api-eu.amazon.com', RegionEnum::EU->baseUrl());
        $this->assertSame('https://advertising-api-fe.amazon.com', RegionEnum::FE->baseUrl());
    }

    public function test_region_enum_sandbox_url_is_same_for_all(): void
    {
        $expected = 'https://advertising-api-test.amazon.com';

        $this->assertSame($expected, RegionEnum::NA->sandboxUrl());
        $this->assertSame($expected, RegionEnum::EU->sandboxUrl());
        $this->assertSame($expected, RegionEnum::FE->sandboxUrl());
    }
}

