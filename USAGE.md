# Примеры использования Amazon Ads API

## Базовая настройка

```php
// config/amazon-ads.php публикуется через:
// php artisan vendor:publish --tag=amazon-ads-config

// .env
AMAZON_ADS_CACHE_STORE=redis   // обязательно redis для Swoole
AMAZON_ADS_TIMEOUT=30
AMAZON_ADS_CONNECT_TIMEOUT=10
```

---

## Credentials

```php
use YourVendor\AmazonAds\Auth\Credentials;

// Готовые регионы
$na  = Credentials::northAmerica('clientId', 'clientSecret', 'refreshToken');
$eu  = Credentials::europe('clientId', 'clientSecret', 'refreshToken');
$fe  = Credentials::farEast('clientId', 'clientSecret', 'refreshToken');

// Произвольный endpoint
$custom = Credentials::make('clientId', 'clientSecret', 'refreshToken', 'https://advertising-api.amazon.com');
```

---

## Через фасад

```php
use YourVendor\AmazonAds\Facades\AmazonAds;
use YourVendor\AmazonAds\Auth\Credentials;

$credentials = Credentials::northAmerica('clientId', 'clientSecret', 'refreshToken');

AmazonAds::authorize($credentials)->profiles()->list();
AmazonAds::authorize($credentials)->profiles()->get('profileId');
AmazonAds::authorize($credentials)->profiles()->update('profileId', ['timezone' => 'UTC']);
```

---

## Через DI (рекомендуется в сервисах)

```php
use YourVendor\AmazonAds\AmazonAds;
use YourVendor\AmazonAds\Auth\Credentials;

class AmazonSyncService
{
    public function __construct(
        private readonly AmazonAds $amazon,
    ) {}

    public function syncCampaigns(Credentials $credentials, string $profileId): array
    {
        return $this->amazon
            ->authorize($credentials)
            ->campaigns($profileId)
            ->list(['stateFilter' => 'enabled']);
    }
}
```

---

## Profiles

```php
$client = AmazonAds::authorize($credentials);

// Получить все профили агентства
$profiles = $client->profiles()->list();

// Получить конкретный профиль
$profile = $client->profiles()->get('1234567890');

// Обновить профиль
$client->profiles()->update('1234567890', [
    'dailyBudget' => 10.00,
    'timezone'    => 'America/Los_Angeles',
]);
```

---

## MarketingStreamSubscriptions

```php
$client    = AmazonAds::authorize($credentials);
$profileId = '1234567890';

// Список подписок профиля
$client->marketingStreamSubscriptions($profileId)->list();

// Создать подписку
$client->marketingStreamSubscriptions($profileId)->create([
    'subscriptionType' => 'SPONSORED_PRODUCTS',
    'destinationArn'   => 'arn:aws:sqs:us-east-1:123456789:my-queue',
    'clientRequestToken' => 'unique-idempotency-token',
]);

// Обновить подписку
$client->marketingStreamSubscriptions($profileId)->update('subscriptionId', [
    'destinationArn' => 'arn:aws:sqs:us-east-1:123456789:new-queue',
]);

// Удалить подписку
$client->marketingStreamSubscriptions($profileId)->delete('subscriptionId');
```

---

## Campaigns

```php
$client    = AmazonAds::authorize($credentials);
$profileId = '1234567890';

// Список кампаний с фильтрами
$client->campaigns($profileId)->list([
    'stateFilter' => 'enabled',
    'count'       => 100,
    'startIndex'  => 0,
]);

// Получить кампанию
$client->campaigns($profileId)->get('campaignId');

// Создать кампанию
$client->campaigns($profileId)->create([
    'name'                => 'My Campaign',
    'campaignType'        => 'sponsoredProducts',
    'targetingType'       => 'manual',
    'state'               => 'enabled',
    'dailyBudget'         => 50.00,
    'startDate'           => '20240101',
]);

// Обновить кампанию
$client->campaigns($profileId)->update('campaignId', [
    'state'       => 'paused',
    'dailyBudget' => 100.00,
]);

// Удалить кампанию
$client->campaigns($profileId)->delete('campaignId');
```

---

## Несколько агентств в одном запросе

```php
// Credentials можно хранить в БД и создавать динамически
$agency1 = Credentials::northAmerica('clientId1', 'secret1', 'refreshToken1');
$agency2 = Credentials::europe('clientId2', 'secret2', 'refreshToken2');

// Полностью изолированы — никаких утечек состояния между ними
$profilesAgency1 = AmazonAds::authorize($agency1)->profiles()->list();
$profilesAgency2 = AmazonAds::authorize($agency2)->profiles()->list();

// Разные профили одного агентства
$campaignsProfile1 = AmazonAds::authorize($agency1)->campaigns('profile1')->list();
$campaignsProfile2 = AmazonAds::authorize($agency1)->campaigns('profile2')->list();
```

---

## Кастомный ресурс

```php
use YourVendor\AmazonAds\Resources\AbstractResource;
use YourVendor\AmazonAds\Auth\Credentials;
use YourVendor\AmazonAds\Http\HttpClient;

final class AdGroups extends AbstractResource
{
    public function __construct(
        HttpClient $client,
        Credentials $credentials,
        private readonly string $profileId,
    ) {
        parent::__construct($client, $credentials);
    }

    public function list(array $query = []): array
    {
        return $this->client->get($this->credentials, '/v2/adGroups', $query, profileId: $this->profileId);
    }

    public function create(array $data): array
    {
        return $this->client->post($this->credentials, '/v2/adGroups', $data, profileId: $this->profileId);
    }
}
```

Добавляешь метод в `AuthorizedClient`:
```php
public function adGroups(string $profileId): AdGroups
{
    return new AdGroups($this->httpClient, $this->credentials, $profileId);
}
```

Используешь как любой другой ресурс:
```php
AmazonAds::authorize($credentials)->adGroups('profileId')->list();
AmazonAds::authorize($credentials)->adGroups('profileId')->create([...]);
```

---

## Обработка ошибок

```php
use YourVendor\AmazonAds\Exceptions\AmazonAdsApiException;
use YourVendor\AmazonAds\Exceptions\AuthException;
use YourVendor\AmazonAds\Exceptions\RateLimitException;

try {
    $result = AmazonAds::authorize($credentials)->campaigns($profileId)->list();

} catch (RateLimitException $e) {
    // HTTP 429 — слишком много запросов
    // Логируй и повтори через backoff
    Log::warning('Amazon Ads rate limit', ['retry_after' => 60]);

} catch (AuthException $e) {
    // Не удалось обновить токен
    // Проверь clientId / clientSecret / refreshToken
    Log::error('Amazon Ads auth failed', ['message' => $e->getMessage()]);

} catch (AmazonAdsApiException $e) {
    // Любая другая ошибка API
    $e->statusCode;    // int — HTTP статус (400, 403, 404, 500...)
    $e->responseBody;  // array — декодированный ответ Amazon
    $e->getMessage();  // string — сообщение об ошибке
}
```

---

## Swoole — важно

В Swoole каждый воркер — отдельный процесс, но корутины внутри него разделяют память.
`AmazonAds`, `HttpClient`, `TokenResolver` — синглтоны без состояния, безопасны.
Токены кешируются в Redis — общий кеш для всех воркеров, рефреш происходит один раз.

```php
// ПРАВИЛЬНО — новый AuthorizedClient на каждый authorize(), изолирован
$client1 = AmazonAds::authorize($agency1); // новый объект
$client2 = AmazonAds::authorize($agency2); // новый объект, не влияет на $client1

// ПРАВИЛЬНО — ресурсы тоже новые объекты каждый раз
$campaigns = $client1->campaigns($profileId); // new Campaigns(...)

// НЕПРАВИЛЬНО — не переиспользуй $campaigns между корутинами/запросами
// Создавай новый через authorize() на каждый запрос
```
