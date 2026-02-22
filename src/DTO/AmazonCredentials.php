<?php

namespace Jonston\AmazonAdsApi\DTO;

use Jonston\AmazonAdsApi\Enums\RegionEnum;

/**
 * Credentials для одного рекламного аккаунта Amazon.
 */
final class AmazonCredentials
{
    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly string $refreshToken,
        public readonly RegionEnum $region = RegionEnum::NA,
    ) {
    }

    public static function fromArray(array $config): self
    {
        return new self(
            clientId: $config['client_id'],
            clientSecret: $config['client_secret'],
            refreshToken: $config['refresh_token'],
            region: RegionEnum::from(strtoupper($config['region'] ?? 'NA')),
        );
    }
}

