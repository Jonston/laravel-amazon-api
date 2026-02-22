<?php

namespace Jonston\AmazonAdsApi\DTO;

use Jonston\AmazonAdsApi\Enums\RegionEnum;

/**
 * Holds credentials for a single Amazon Ads account.
 *
 * Use RegionEnum to resolve the base URL automatically,
 * or pass a custom base_url directly for non-standard endpoints.
 */
final readonly class AmazonCredentials
{
    public const TOKEN_ENDPOINT = 'https://api.amazon.com/auth/o2/token';

    /**
     * @param string $clientId  Amazon Advertising API client ID
     * @param string $clientSecret  OAuth client secret
     * @param string $refreshToken  Long-lived refresh token
     * @param string $baseUrl Amazon Ads API base URL
     * @param string $tokenEndpoint OAuth token endpoint (overridable for testing)
     */
    public function __construct(
        public string $clientId,
        public string $clientSecret,
        public string $refreshToken,
        public string $baseUrl,
        public string $tokenEndpoint = self::TOKEN_ENDPOINT,
    ) {}

    /**
     * Create credentials using a RegionEnum to resolve the base URL.
     *
     * @param RegionEnum $region
     * @param string  $clientId
     * @param string $clientSecret
     * @param string $refreshToken
     * @param bool $sandbox Use the sandbox endpoint when true
     * @return self
     */
    public static function fromRegion(
        RegionEnum $region,
        string $clientId,
        string $clientSecret,
        string $refreshToken,
        bool $sandbox = false,
    ): self {
        return new self(
            clientId: $clientId,
            clientSecret: $clientSecret,
            refreshToken: $refreshToken,
            baseUrl: $sandbox ? $region->sandboxUrl() : $region->baseUrl(),
        );
    }

    /**
     * Create credentials from a configuration array.
     *
     * Accepts either a 'region' key (resolved via RegionEnum)
     * or a 'base_url' key for a custom endpoint.
     *
     * @param array $config
     * @param bool $sandbox
     * @return self
     */
    public static function fromArray(array $config, bool $sandbox = false): self
    {
        if (isset($config['base_url'])) {
            return new self(
                clientId: $config['client_id'],
                clientSecret: $config['client_secret'],
                refreshToken: $config['refresh_token'],
                baseUrl: $config['base_url'],
                tokenEndpoint: $config['token_endpoint'] ?? self::TOKEN_ENDPOINT,
            );
        }

        $region = RegionEnum::from(strtoupper($config['region'] ?? 'NA'));

        return self::fromRegion(
            region: $region,
            clientId: $config['client_id'],
            clientSecret: $config['client_secret'],
            refreshToken: $config['refresh_token'],
            sandbox: $sandbox,
        );
    }
}
