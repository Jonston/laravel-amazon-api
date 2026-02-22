<?php

namespace Jonston\AmazonAdsApi;

use Illuminate\Support\ServiceProvider;

class AmazonAdsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/amazon-ads-api.php',
            'amazon-ads-api'
        );

        $this->app->singleton(AmazonAds::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/amazon-ads-api.php' => config_path('amazon-ads-api.php'),
            ], 'amazon-ads-api-config');
        }
    }
}