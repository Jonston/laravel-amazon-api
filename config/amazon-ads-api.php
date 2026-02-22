<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    | Если true — все запросы идут на https://advertising-api-test.amazon.com
    */
    'sandbox' => env('AMAZON_SANDBOX', false),

    /*
    |--------------------------------------------------------------------------
    | Рекламные аккаунты
    |--------------------------------------------------------------------------
    | Агентство может управлять несколькими рекламными аккаунтами Amazon.
    | Каждый аккаунт задаётся под уникальным ключом.
    |
    | Регионы: NA (North America), EU (Europe), FE (Far East)
    |
    | Пример динамической регистрации аккаунта из БД:
    |   app(AmazonManager::class)->addAccount('client-x', new AmazonCredentials(...))
    */
    'accounts' => [
        'default' => [
            'client_id' => env('AMAZON_CLIENT_ID'),
            'client_secret' => env('AMAZON_CLIENT_SECRET'),
            'refresh_token' => env('AMAZON_REFRESH_TOKEN'),
            'region' => env('AMAZON_REGION', 'NA'),
        ],

        // Пример второго аккаунта:
        // 'agency-client-eu' => [
        //     'client_id'     => env('AMAZON_EU_CLIENT_ID'),
        //     'client_secret' => env('AMAZON_EU_CLIENT_SECRET'),
        //     'refresh_token' => env('AMAZON_EU_REFRESH_TOKEN'),
        //     'region'        => 'EU',
        // ],
    ],
];
