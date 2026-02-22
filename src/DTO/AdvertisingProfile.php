<?php

namespace Jonston\AmazonAdsApi\DTO;

use Jonston\AmazonAdsApi\Enums\RegionEnum;

/**
 * Represents an Amazon Advertising profile tied to a specific region.
 *
 * The region determines which API endpoint will be used for profile-scoped requests
 * (e.g. Marketing Stream subscriptions). Always use the region that matches
 * the profile, regardless of the agency credentials region.
 */
final readonly class AdvertisingProfile
{
    /**
     * @param string     $profileId Amazon Advertising API Scope identifier
     * @param RegionEnum $region    Region this profile belongs to
     */
    public function __construct(
        public string $profileId,
        public RegionEnum $region,
    ) {}

    /**
     * Create from an array (e.g. from a profiles()->list() API response or database row).
     *
     * @param array $data Must contain 'profileId' and 'countryCode' or 'region' keys
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $region = isset($data['region'])
            ? RegionEnum::from(strtoupper($data['region']))
            : RegionEnum::fromCountryCode($data['countryCode']);

        return new self(
            profileId: (string) $data['profileId'],
            region: $region,
        );
    }
}

