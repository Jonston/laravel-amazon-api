<?php

namespace Jonston\AmazonAdsApi\Enums;

/**
 * Amazon Ads API region.
 *
 * Helper for resolving the correct base URL per region.
 * For custom endpoints pass the URL directly to AmazonCredentials.
 */
enum RegionEnum: string
{
    case NA = 'NA';
    case EU = 'EU';
    case FE = 'FE';

    /**
     * Resolve a region from an Amazon country code (e.g. 'US', 'GB', 'JP').
     *
     * @param string $countryCode ISO 3166-1 alpha-2 country code
     * @return self
     */
    public static function fromCountryCode(string $countryCode): self
    {
        return match (strtoupper($countryCode)) {
            'US', 'CA', 'MX', 'BR' => self::NA,
            'JP', 'AU', 'SG'       => self::FE,
            default                => self::EU,
        };
    }

    /**
     * Return the production base URL for this region.
     *
     * @return string
     */
    public function baseUrl(): string
    {
        return match ($this) {
            self::NA => 'https://advertising-api.amazon.com',
            self::EU => 'https://advertising-api-eu.amazon.com',
            self::FE => 'https://advertising-api-fe.amazon.com',
        };
    }

    /**
     * Return the sandbox base URL (shared across all regions).
     *
     * @return string
     */
    public function sandboxUrl(): string
    {
        return 'https://advertising-api-test.amazon.com';
    }
}
