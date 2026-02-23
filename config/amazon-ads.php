<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default HTTP timeout (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => env('AMAZON_ADS_TIMEOUT', 30),

    'connect_timeout' => env('AMAZON_ADS_CONNECT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Cache store for access tokens
    | Use 'array' for testing, 'redis' or 'memcached' for production.
    | In Swoole environments use a shared store (redis), NOT 'array'.
    |--------------------------------------------------------------------------
    */
    'cache_store' => env('AMAZON_ADS_CACHE_STORE', env('CACHE_STORE', 'file')),
];
