# Amazon Ads API — Laravel Package

Low-level Laravel package for [Amazon Advertising API](https://advertising.amazon.com/API/docs).

Designed for agencies and platforms managing **multiple Amazon ad accounts**. Switch between accounts via the fluent `->authorize()` method. Each advertising profile carries its own region, so the correct API endpoint is resolved automatically — no manual endpoint management required.

---

## Requirements

- PHP **8.2+**
- Laravel **10** or **11**

---

## Installation

```bash
composer require jonston/amazon-ads-api
```

The package registers itself automatically via Laravel Package Auto-Discovery.

Publish the config (optional):

```bash
php artisan vendor:publish --tag=amazon-ads-api-config
```

---

## Configuration

After publishing, the config is located at `config/amazon-ads-api.php`.

### Environment variables

```dotenv
# Sandbox mode — all requests go to advertising-api-test.amazon.com
AMAZON_SANDBOX=false

# Default credentials (for single-account scenarios)
AMAZON_CLIENT_ID=amzn1.application-oa2-client.xxxxx
AMAZON_CLIENT_SECRET=xxxxx
AMAZON_REFRESH_TOKEN=Atzr|xxxxx
AMAZON_REGION=NA
```

### Regions

| Value | Production endpoint |
|-------|---------------------|
| `NA`  | `https://advertising-api.amazon.com` |
| `EU`  | `https://advertising-api-eu.amazon.com` |
| `FE`  | `https://advertising-api-fe.amazon.com` |
| Sandbox | `https://advertising-api-test.amazon.com` |

---

## Core concept

The central object is `AmazonAds`. It is registered as a **singleton** in the Laravel container. Before each request, set the account context via `->authorize(AmazonCredentials $credentials)`.

Each advertising profile (`AdvertisingProfile`) carries its own `RegionEnum`. When calling `->marketingStreamSubscriptions($profile)`, the HTTP client automatically switches to the correct regional endpoint for that profile — independent of the agency credentials region.

```
AmazonAds
  ->authorize(AmazonCredentials)              ← switch account
  ->profiles()                                ← ProfileResource
  ->marketingStreamSubscriptions(profile)     ← MarketingStreamSubscriptionResource
  ->client()                                  ← direct HTTP client access
```

---

## Quick start

### List profiles

```php
use Jonston\AmazonAdsApi\AmazonAds;
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\Enums\RegionEnum;

$credentials = AmazonCredentials::fromRegion(
    region: RegionEnum::NA,
    clientId: 'amzn1.application-oa2-client.xxxxx',
    clientSecret: 'your-client-secret',
    refreshToken: 'Atzr|your-refresh-token',
);

$profiles = app(AmazonAds::class)
    ->authorize($credentials)
    ->profiles()
    ->list();
```

Or via the helper function:

```php
$profiles = amazon_ads()
    ->authorize($credentials)
    ->profiles()
    ->list();
```

---

## AmazonCredentials

DTO holding the credentials for one Amazon Ads account. Three ways to create it:

### 1. Via RegionEnum (recommended)

```php
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\Enums\RegionEnum;

$credentials = AmazonCredentials::fromRegion(
    region: RegionEnum::EU,
    clientId: 'amzn1.application-oa2-client.xxxxx',
    clientSecret: 'your-secret',
    refreshToken: 'Atzr|your-token',
);

// Sandbox
$credentials = AmazonCredentials::fromRegion(
    region: RegionEnum::NA,
    clientId: 'amzn1.application-oa2-client.xxxxx',
    clientSecret: 'your-secret',
    refreshToken: 'Atzr|your-token',
    sandbox: true,
);
```

### 2. Via array (convenient for database rows)

```php
// With region key
$credentials = AmazonCredentials::fromArray([
    'client_id' => 'amzn1.application-oa2-client.xxxxx',
    'client_secret' => 'your-secret',
    'refresh_token' => 'Atzr|your-token',
    'region' => 'NA',
]);

// With explicit base_url
$credentials = AmazonCredentials::fromArray([
    'client_id' => 'amzn1.application-oa2-client.xxxxx',
    'client_secret' => 'your-secret',
    'refresh_token' => 'Atzr|your-token',
    'base_url' => 'https://advertising-api-eu.amazon.com',
]);

// Sandbox
$credentials = AmazonCredentials::fromArray($account->toArray(), sandbox: true);
```

### 3. Via constructor

```php
$credentials = new AmazonCredentials(
    clientId: 'amzn1.application-oa2-client.xxxxx',
    clientSecret: 'your-secret',
    refreshToken: 'Atzr|your-token',
    baseUrl: 'https://advertising-api.amazon.com',
    // tokenEndpoint: 'https://api.amazon.com/auth/o2/token', // overridable
);
```

---

## AdvertisingProfile

DTO representing an advertising profile with its region.

Amazon profiles belong to specific regions (NA, EU, FE). The region determines which API endpoint must be used for profile-scoped requests. Always use `AdvertisingProfile` instead of a raw profile ID string.

### Create manually

```php
use Jonston\AmazonAdsApi\DTO\AdvertisingProfile;
use Jonston\AmazonAdsApi\Enums\RegionEnum;

$profile = new AdvertisingProfile('1234567890', RegionEnum::EU);
```

### Create from an API response or database row

```php
// Amazon profiles()->list() returns countryCode (e.g. 'US', 'GB', 'JP')
// AdvertisingProfile resolves the correct region automatically
$profile = AdvertisingProfile::fromArray([
    'profileId' => '1234567890',
    'countryCode' => 'GB', // → RegionEnum::EU
]);

$profile = AdvertisingProfile::fromArray([
    'profileId' => '9876543210',
    'countryCode' => 'US', // → RegionEnum::NA
]);

// Or with an explicit region key
$profile = AdvertisingProfile::fromArray([
    'profileId' => '1111111111',
    'region' => 'FE',
]);
```

### Country code to region mapping

| Country codes | Region |
|---------------|--------|
| US, CA, MX, BR | `NA` |
| JP, AU, SG | `FE` |
| All others (GB, DE, FR, IT, ES…) | `EU` |

---

## Agency scenario (multi-account, multi-region)

An agency manages N client accounts, each with profiles in different regions.
Credentials are stored in the database and passed dynamically. The correct regional endpoint is resolved automatically per profile.

```php
use Jonston\AmazonAdsApi\AmazonAds;
use Jonston\AmazonAdsApi\DTO\AdvertisingProfile;
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\DTO\CreateSubscriptionData;

class AgencyAmazonService
{
    public function __construct(private readonly AmazonAds $amazon)
    {
    }

    public function getProfiles(Account $account): array
    {
        return $this->amazon
            ->authorize(AmazonCredentials::fromArray($account->amazon_credentials))
            ->profiles()
            ->list();
    }

    public function createSubscription(Account $account, AdvertisingProfile $profile): array
    {
        $data = new CreateSubscriptionData(
            dataSetId: 'SPONSORED_PRODUCTS_CAMPAIGN_DIAGNOSTICS',
            destinationType: 'SQS',
            destinationArn: $account->sqs_arn,
        );

        return $this->amazon
            ->authorize(AmazonCredentials::fromArray($account->amazon_credentials))
            ->marketingStreamSubscriptions($profile) // endpoint switches to profile's region
            ->create($data);
    }
}
```

Switching between accounts and profiles in one request:

```php
$amazon = app(AmazonAds::class);

$profileEU = new AdvertisingProfile('111', RegionEnum::EU);
$profileNA = new AdvertisingProfile('222', RegionEnum::NA);

// Agency A — credentials can be any region; the profile region drives the endpoint
$amazon->authorize($credentialsA)->marketingStreamSubscriptions($profileEU)->create($data);
$amazon->authorize($credentialsA)->marketingStreamSubscriptions($profileNA)->create($data);

// Agency B
$amazon->authorize($credentialsB)->profiles()->list();
```

---

## Resources

### ProfileResource

Access: `->profiles()`

| Method | Description |
|--------|-------------|
| `list(): array` | Return all profiles for the account |
| `get(string $profileId): array` | Return a single profile by ID |

```php
$amazon->authorize($credentials)->profiles()->list();
$amazon->authorize($credentials)->profiles()->get('1234567890');
```

### MarketingStreamSubscriptionResource

Access: `->marketingStreamSubscriptions(AdvertisingProfile $profile)`

The resource is scoped to the profile's `profileId` via the `Amazon-Advertising-API-Scope` header.
The HTTP client endpoint is automatically set to the profile's region.

| Method | Description |
|--------|-------------|
| `list(array $params = []): array` | Return a list of subscriptions |
| `get(string $id): array` | Return a single subscription by ID |
| `create(CreateSubscriptionData $data): array` | Create a subscription |
| `update(string $id, UpdateSubscriptionData $data): array` | Update a subscription |
| `delete(string $id): array` | Delete a subscription |

```php
use Jonston\AmazonAdsApi\DTO\AdvertisingProfile;
use Jonston\AmazonAdsApi\DTO\CreateSubscriptionData;
use Jonston\AmazonAdsApi\DTO\UpdateSubscriptionData;
use Jonston\AmazonAdsApi\Enums\RegionEnum;

$profile = new AdvertisingProfile('1234567890', RegionEnum::EU);

$resource = $amazon
    ->authorize($credentials)
    ->marketingStreamSubscriptions($profile);

// List
$resource->list();
$resource->list(['destinationType' => 'SQS']);

// Get
$resource->get('sub-id-123');

// Create
$resource->create(new CreateSubscriptionData(
    dataSetId: 'SPONSORED_PRODUCTS_CAMPAIGN_DIAGNOSTICS',
    destinationType: 'SQS',
    destinationArn: 'arn:aws:sqs:eu-west-1:123456789:my-queue',
    notes: 'Agency client A',
));

// Update
$resource->update('sub-id-123', new UpdateSubscriptionData(
    destinationArn: 'arn:aws:sqs:eu-west-1:123456789:new-queue',
));

// Delete
$resource->delete('sub-id-123');
```

---

## Subscription DTOs

### CreateSubscriptionData

| Property | Type | Required | Description |
|----------|------|----------|-------------|
| `dataSetId` | `string` | ✓ | Marketing Stream dataset identifier |
| `destinationType` | `string` | ✓ | Destination type (e.g. `SQS`) |
| `destinationArn` | `string` | ✓ | ARN of the destination resource |
| `notes` | `string\|null` | — | Optional notes |

### UpdateSubscriptionData

Only non-null fields are included in the request payload.

| Property | Type | Description |
|----------|------|-------------|
| `destinationArn` | `string\|null` | New ARN of the destination resource |
| `notes` | `string\|null` | Updated notes |

```php
// Update only the ARN
new UpdateSubscriptionData(destinationArn: 'arn:aws:sqs:...');

// Update only the notes
new UpdateSubscriptionData(notes: 'Updated');

// Update both
new UpdateSubscriptionData(
    destinationArn: 'arn:aws:sqs:...',
    notes: 'Updated',
);
```

---

## OAuth — exchange code for tokens

If you implement the OAuth Authorization Code Flow (user grants access via Amazon):

```php
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\Enums\RegionEnum;

$credentials = new AmazonCredentials(
    clientId: 'amzn1.application-oa2-client.xxxxx',
    clientSecret: 'your-secret',
    refreshToken: '',
    baseUrl: RegionEnum::NA->baseUrl(),
);

$tokens = amazon_ads()
    ->authorize($credentials)
    ->client()
    ->oauth()
    ->exchangeCode(
        code: $request->get('code'),
        redirectUri: route('amazon.callback'),
    );

// $tokens['access_token']
// $tokens['refresh_token']  ← persist to database
```

---

## Access token

The package automatically obtains an `access_token` via the `refresh_token` before each request and **caches it for 55 minutes** (tokens expire after 60 minutes). The standard Laravel Cache driver is used.

The cache key is unique per `clientId + refreshToken` pair, so different accounts never conflict.

---

## Raw HTTP requests

For endpoints not yet covered by a resource class, use the client directly:

```php
$result = amazon_ads()
    ->authorize($credentials)
    ->client()
    ->request('GET', '/v2/profiles');

// With a scope header
$result = amazon_ads()
    ->authorize($credentials)
    ->client()
    ->withHeaders(['Amazon-Advertising-API-Scope' => '1234567890'])
    ->request('GET', '/v2/campaigns', ['query' => ['stateFilter' => 'enabled']]);
```

`withHeaders()` is immutable — it returns a new client instance, the original is unchanged.

---

## Error handling

All exceptions extend `AmazonApiException`:

```php
use Jonston\AmazonAdsApi\Exceptions\AmazonApiException;
use Illuminate\Http\Client\ConnectionException;

try {
    $profiles = amazon_ads()->authorize($credentials)->profiles()->list();
} catch (AmazonApiException $e) {
    // HTTP error from the API or OAuth failure
    Log::error('Amazon API error', ['message' => $e->getMessage()]);
} catch (ConnectionException $e) {
    // Network error / timeout
    Log::error('Amazon API connection failed', ['message' => $e->getMessage()]);
}
```

---

## Package structure

```
src/
├── AmazonAds.php                                ← entry point, fluent, singleton
├── AmazonAdsServiceProvider.php                 ← registers AmazonAds
├── AmazonClient.php                             ← HTTP transport, immutable
├── OAuthClient.php                              ← OAuth 2.0 token operations
├── DTO/
│   ├── AdvertisingProfile.php                  ← profile with region
│   ├── AmazonCredentials.php                   ← account credentials
│   ├── CreateSubscriptionData.php              ← subscription create payload
│   └── UpdateSubscriptionData.php              ← subscription update payload
├── Enums/
│   └── RegionEnum.php                          ← region → base URL helper
├── Exceptions/
│   └── AmazonApiException.php                  ← custom exceptions
├── Resources/
│   ├── ProfileResource.php                     ← /v2/profiles
│   └── MarketingStreamSubscriptionResource.php ← /streams/subscriptions
└── helpers.php                                 ← amazon_ads()
```

---

## Testing

```bash
./vendor/bin/phpunit
```

Use `Http::fake()` to mock HTTP calls in tests:

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    'advertising-api.amazon.com/v2/profiles' => Http::response([
        ['profileId' => '123', 'countryCode' => 'US'],
    ], 200),
    'advertising-api-eu.amazon.com/streams/subscriptions' => Http::response([
        'subscriptionId' => 'sub-abc',
    ], 200),
    'api.amazon.com/auth/o2/token' => Http::response([
        'access_token' => 'fake-token',
    ], 200),
]);

$profile = AdvertisingProfile::fromArray(['profileId' => '123', 'countryCode' => 'US']);

amazon_ads()->authorize($credentials)->marketingStreamSubscriptions($profile)->list();
```

---

## License

MIT
