<?php

namespace Jonston\AmazonAdsApi\Enums;

enum RegionEnum: string
{
    case NA = 'NA';
    case EU = 'EU';
    case FE = 'FE';

    public function getBaseUrl(): string
    {
        return match ($this) {
            self::NA => 'advertising-api.amazon.com',
            self::EU => 'advertising-api-eu.amazon.com',
            self::FE => 'advertising-api-fe.amazon.com',
        };
    }
}
