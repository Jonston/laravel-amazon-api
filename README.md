# Amazon Ads API — Laravel Package

Low-level, flexible, Swoole-safe Laravel package for Amazon Ads API.

## Installation

```bash
composer require your-vendor/amazon-ads
php artisan vendor:publish --tag=amazon-ads-config
```

## Architecture

```
AmazonAds (stateless singleton)
  └── authorize(Credentials) → AuthorizedClient (immutable, per credentials)
        ├── profiles()                              → Profiles resource
        ├── marketingStreamSubscriptions($profileId) → MarketingStreamSubscriptions resource
        ├── campaigns($profileId)                   → Campaigns resource
        └── resource(MyCustomResource::class)       → any custom Resource
```

Key design decisions:
- **AmazonAds** is a stateless singleton — safe in Swoole
- **AuthorizedClient** is created fresh per `authorize()` call — no shared state between agencies/clients
- **Resources** are cloned via `withClient()` — each call gets a fresh isolated instance
- **TokenResolver** caches tokens in Laravel Cache (use Redis in Swoole, not `array`)

## Basic usage

```php
use YourVendor\AmazonAds\Auth\Credentials;
use YourVendor\AmazonAds\Facades\AmazonAds;

// Define credentials per agency / client
$agency1 = Credentials::northAmerica(
    clientId:     'amzn1.application-oa2-client.xxx',
    clientSecret: 'secret1',
    refreshToken: 'Atzr|...',
);

$agency2 = Credentials::europe(
    clientId:     'amzn1.application-oa2-client.yyy',
    clientSecret: 'secret2',
    refreshToken: 'Atzr|...',
);

// Or with a custom endpoint
$custom = Credentials::make(
    clientId:     'amzn1...',
    clientSecret: 'secret',
    refreshToken: 'Atzr|...',
    endpoint:     'https://advertising-api-fe.amazon.com',
);
```

```php
// List profiles for agency 1
$profiles = AmazonAds::authorize($agency1)->profiles()->list();

// Work with a specific profile
$profileId = '1234567890';

AmazonAds::authorize($agency1)
    ->marketingStreamSubscriptions($profileId)
    ->create([
        'subscriptionType' => 'SPONSORED_PRODUCTS',
        'destinationArn'   => 'arn:aws:sqs:...',
    ]);

// Switch to agency 2 — completely isolated, no state leak
AmazonAds::authorize($agency2)
    ->marketingStreamSubscriptions($profileId)
    ->update($subscriptionId, $data);

// Same call pattern, different credentials
AmazonAds::authorize($agency1)->campaigns($profileId)->list(['stateFilter' => 'enabled']);
AmazonAds::authorize($agency2)->campaigns($profileId)->list(['stateFilter' => 'paused']);
```

## Adding custom resources

```php
use YourVendor\AmazonAds\Resources\AbstractResource;

final class AdGroups extends AbstractResource
{
    private string $profileId;

    public function forProfile(string $profileId): static
    {
        $clone = clone $this;
        $clone->profileId = $profileId;
        return $clone;
    }

    public function list(): array
    {
        return $this->client->get($this->credentials, '/v2/adGroups', profileId: $this->profileId);
    }
}

// Register in your own AuthorizedClient extension or use the escape hatch:
$adGroups = AmazonAds::authorize($agency1)
    ->resource(AdGroups::class)
    ->forProfile($profileId)
    ->list();
```

## Swoole considerations

| Component       | Strategy                                         |
|-----------------|--------------------------------------------------|
| `AmazonAds`     | Singleton — stateless, safe                      |
| `HttpClient`    | Singleton — no mutable state, all via args       |
| `TokenResolver` | Singleton — uses injected Cache (use Redis!)     |
| `AuthorizedClient` | Created per `authorize()` call — never shared |
| Resources       | Cloned per call via `withClient()` — never shared |

**Important:** Set `AMAZON_ADS_CACHE_STORE=redis` in production Swoole environments.
The `array` cache driver is process-local and will cause tokens to be re-fetched on every request.

## Exception hierarchy

```
AmazonAdsApiException
├── AuthException       — token refresh failed
└── RateLimitException  — HTTP 429
```

```php
use YourVendor\AmazonAds\Exceptions\AmazonAdsApiException;
use YourVendor\AmazonAds\Exceptions\RateLimitException;

try {
    AmazonAds::authorize($credentials)->profiles()->list();
} catch (RateLimitException $e) {
    // retry after backoff
} catch (AmazonAdsApiException $e) {
    $e->statusCode;    // HTTP status
    $e->responseBody;  // decoded response array
}
```
