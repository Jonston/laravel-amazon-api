<?php

use Jonston\AmazonAdsApi\AmazonAds;

if (!function_exists('amazon_ads')) {
    /**
     * Получить синглтон AmazonAds из контейнера.
     *
     * Использование:
     *   amazon_ads()->authorize($credentials)->profiles()->list();
     */
    function amazon_ads(): AmazonAds
    {
        return app(AmazonAds::class);
    }
}
