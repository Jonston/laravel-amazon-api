<?php

namespace Jonston\AmazonAdsApi\Tests;

use Jonston\AmazonAdsApi\AmazonAdsServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [AmazonAdsServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('amazon-ads-api.sandbox', true);
        $app['config']->set('amazon-ads-api.default', [
            'client_id'     => 'test-client-id',
            'client_secret' => 'test-client-secret',
            'refresh_token' => 'test-refresh-token',
            'region'        => 'NA',
        ]);
    }
}
