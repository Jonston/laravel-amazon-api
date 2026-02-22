<?php

namespace Jonston\AmazonAdsApi;

use Illuminate\Support\ServiceProvider;
use Jonston\AmazonAdsApi\Contracts\AmazonManagerContract;

class AmazonAdsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/amazon-ads-api.php',
            'amazon-ads-api'
        );

        $this->app->singleton(AmazonManager::class, function ($app) {
            return new AmazonManager(config('amazon-ads-api'));
        });

        // Привязываем контракт к реализации
        $this->app->alias(AmazonManager::class, AmazonManagerContract::class);
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