<?php

declare(strict_types=1);

namespace Jonston\AmazonAds\Facades;

use Illuminate\Support\Facades\Facade;
use Jonston\AmazonAds\AmazonAds as AmazonAdsClient;
use Jonston\AmazonAds\Auth\Credentials;
use Jonston\AmazonAds\AuthorizedClient;

/**
 * @method static AuthorizedClient authorize(Credentials $credentials)
 *
 * @see AmazonAdsClient
 */
final class AmazonAds extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AmazonAdsClient::class;
    }
}
