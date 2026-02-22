<?php

namespace YourVendor\AmazonApi\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use YourVendor\AmazonApi\AmazonApiServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [AmazonApiServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('amazon-api.client_id', 'test-client-id');
        $app['config']->set('amazon-api.client_secret', 'test-client-secret');
        $app['config']->set('amazon-api.refresh_token', 'test-refresh-token');
        $app['config']->set('amazon-api.region', 'na');
    }
}

