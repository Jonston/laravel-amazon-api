<?php

namespace Jonston\AmazonAdsApi\DTO;

use Jonston\AmazonAdsApi\Enums\RegionEnum;

/**
 * Credentials одного рекламного аккаунта Amazon.
 *
 * baseUrl — endpoint Amazon Ads API для этого аккаунта.
 * Можно задать напрямую или использовать RegionEnum как хелпер:
 *
 *   AmazonCredentials::fromRegion(RegionEnum::EU, clientId: '...', ...)
 *   new AmazonCredentials(baseUrl: 'https://advertising-api-eu.amazon.com', ...)
 *
 * tokenEndpoint — стандартный Amazon OAuth endpoint, одинаков для всех регионов,
 * но может быть переопределён если нужно (например, sandbox или mock в тестах).
 */
final readonly class AmazonCredentials
{
    public const TOKEN_ENDPOINT = 'https://api.amazon.com/auth/o2/token';

    public function __construct(
        public string $clientId,
        public string $clientSecret,
        public string $refreshToken,
        public string $baseUrl,
        public string $tokenEndpoint = self::TOKEN_ENDPOINT,
    ) {
    }

    /**
     * Создать из RegionEnum — удобно когда регион известен заранее.
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
     * Создать из массива конфига.
     *
     * Поддерживает как 'base_url' напрямую, так и 'region' для автоматического выбора URL.
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
