<?php

namespace Jonston\AmazonAdsApi\Enums;

enum RegionEnum: string
{
    case NA = 'NA';
    case EU = 'EU';
    case FE = 'FE';

    public function getBaseUrl(bool $sandbox = false): string
    {
        if ($sandbox) {
            return match ($this) {
                self::NA => 'https://advertising-api-test.amazon.com',
                self::EU => 'https://advertising-api-test.amazon.com',
                self::FE => 'https://advertising-api-test.amazon.com',
            };
        }

        return match ($this) {
            self::NA => 'https://advertising-api.amazon.com',
            self::EU => 'https://advertising-api-eu.amazon.com',
            self::FE => 'https://advertising-api-fe.amazon.com',
        };
    }

    public function getTokenEndpoint(): string
    {
        return 'https://api.amazon.com/auth/o2/token';
    }
}
