<?php

namespace Jonston\AmazonAdsApi;

use Illuminate\Support\ServiceProvider;
use Jonston\AmazonAdsApi\AmazonAds;

class AmazonAdsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Слияние конфига
        $this->mergeConfigFrom(__DIR__.'/../config/amazon-ads-api.php', 'amazon-ads');

        // Регистрация основного клиента
        $this->app->singleton(AmazonAds::class, function ($app) {
            $config = config('amazon-ads-api');

            // Здесь можно добавить логику получения access_token из кэша
            $client = new AmazonClient(
                $config['client_id'],
                'CURRENT_ACCESS_TOKEN', // Токен обычно передается динамически
                $config['region']
            );

            return new AmazonAds($client);
        });
    }

    public function boot(): void
    {
        // Публикация конфига
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/amazon-ads.php' => config_path('amazon-ads.php'),
            ], 'amazon-ads-config');
        }
    }
}