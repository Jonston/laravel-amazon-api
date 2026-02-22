<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    | Если true — base URL для RegionEnum::*->sandboxUrl() будет использован
    | автоматически при AmazonCredentials::fromRegion(..., sandbox: true).
    |
    | Прямая передача base_url в AmazonCredentials игнорирует этот флаг.
    */
    'sandbox' => env('AMAZON_SANDBOX', false),

    /*
    |--------------------------------------------------------------------------
    | Default Credentials (опционально)
    |--------------------------------------------------------------------------
    | Если вы работаете только с одним аккаунтом, можно задать credentials здесь.
    | Для multi-tenant сценариев (агентство → N аккаунтов) создавайте
    | AmazonCredentials динамически и передавайте через ->authorize():
    |
    |   $amazon->authorize(AmazonCredentials::fromRegion(RegionEnum::NA, ...))
    |   $amazon->authorize(AmazonCredentials::fromArray($accountFromDatabase))
    */
    'default' => [
        'client_id'     => env('AMAZON_CLIENT_ID'),
        'client_secret' => env('AMAZON_CLIENT_SECRET'),
        'refresh_token' => env('AMAZON_REFRESH_TOKEN'),
        'region'        => env('AMAZON_REGION', 'NA'),
        // 'base_url'   => env('AMAZON_BASE_URL'), // переопределить endpoint напрямую
    ],

];
