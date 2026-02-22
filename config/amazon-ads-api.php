<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Amazon Ads API Credentials
    |--------------------------------------------------------------------------
    */
    'client_id' => env('AMAZON_CLIENT_ID'),
    'client_secret' => env('AMAZON_CLIENT_SECRET'),
    'refresh_token' => env('AMAZON_REFRESH_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Marketplace & Region
    |--------------------------------------------------------------------------
    | Regions: na (North America), eu (Europe), fe (Far East)
    */
    'region' => env('AMAZON_REGION', 'na'),

    /*
    |--------------------------------------------------------------------------
    | Endpoints
    |--------------------------------------------------------------------------
    */
    'ads_endpoint' => env('AMAZON_ADS_ENDPOINT', 'https://advertising-api{region}.amazon.com'),

    'token_endpoint' => 'https://api.amazon.com/auth/o2/token',

    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    */
    'sandbox' => env('AMAZON_SANDBOX', false),

];
