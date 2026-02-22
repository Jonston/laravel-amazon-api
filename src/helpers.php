<?php

use Jonston\AmazonAdsApi\AmazonAds;
use Jonston\AmazonAdsApi\AmazonManager;

if (!function_exists('amazon_ads')) {
    /**
     * Получить экземпляр AmazonAds для указанного аккаунта.
     *
     * @param  string $account  Имя аккаунта из конфига amazon-ads-api.accounts
     * @return AmazonAds
     */
    function amazon_ads(string $account = 'default'): AmazonAds
    {
        return app(AmazonManager::class)->account($account);
    }
}

