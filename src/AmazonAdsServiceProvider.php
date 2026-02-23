<?php

declare(strict_types=1);

namespace Jonston\AmazonAds;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;
use Jonston\AmazonAds\Auth\TokenResolver;
use Jonston\AmazonAds\Http\HttpClient;

final class AmazonAdsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/amazon-ads.php', 'amazon-ads');

        // TokenResolver — singleton is fine because it's stateless (cache is injected)
        $this->app->singleton(TokenResolver::class, function ($app) {
            return new TokenResolver(
                cache: $app['cache']->store(config('amazon-ads.cache_store')),
                httpClient: $app->make(HttpFactory::class),
            );
        });

        // HttpClient — singleton is safe: no mutable state, all context passed per-call
        $this->app->singleton(HttpClient::class, function ($app) {
            return new HttpClient(
                http: $app->make(HttpFactory::class),
                tokenResolver: $app->make(TokenResolver::class),
            );
        });

        // AmazonAds — singleton entry point, fully stateless
        $this->app->singleton(AmazonAds::class, function ($app) {
            return new AmazonAds(
                httpClient: $app->make(HttpClient::class),
            );
        });

        $this->app->alias(AmazonAds::class, 'amazon-ads');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/amazon-ads.php' => config_path('amazon-ads.php'),
            ], 'amazon-ads-config');
        }
    }
}
