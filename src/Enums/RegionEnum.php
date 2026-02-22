<?php

namespace Jonston\AmazonAdsApi\Enums;

/**
 * Регион Amazon Ads API.
 *
 * Используется как удобный хелпер для получения base URL.
 * Если нужен нестандартный endpoint — передайте его напрямую в AmazonCredentials.
 */
enum RegionEnum: string
{
    case NA = 'NA';
    case EU = 'EU';
    case FE = 'FE';

    public function baseUrl(): string
    {
        return match ($this) {
            self::NA => 'https://advertising-api.amazon.com',
            self::EU => 'https://advertising-api-eu.amazon.com',
            self::FE => 'https://advertising-api-fe.amazon.com',
        };
    }

    public function sandboxUrl(): string
    {
        return 'https://advertising-api-test.amazon.com';
    }
}
