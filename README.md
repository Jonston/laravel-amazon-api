# Amazon Ads API — Laravel Package

Низкоуровневый Laravel-пакет для работы с [Amazon Advertising API](https://advertising.amazon.com/API/docs).

Спроектирован для агентств и платформ, управляющих **множеством рекламных аккаунтов** Amazon. Переключение между аккаунтами происходит через fluent-метод `->authorize()` без создания новых объектов.

---

## Требования

- PHP **8.2+**
- Laravel **10** или **11**

---

## Установка

```bash
composer require jonston/amazon-ads-api
```

Пакет автоматически регистрируется через Laravel Package Auto-Discovery.

Опубликовать конфиг (опционально):

```bash
php artisan vendor:publish --tag=amazon-ads-api-config
```

---

## Конфигурация

После публикации конфиг находится в `config/amazon-ads-api.php`.

### Переменные окружения `.env`

```dotenv
# Sandbox-режим (все запросы идут на advertising-api-test.amazon.com)
AMAZON_SANDBOX=false

# Default credentials (для single-account сценария)
AMAZON_CLIENT_ID=amzn1.application-oa2-client.xxxxx
AMAZON_CLIENT_SECRET=xxxxx
AMAZON_REFRESH_TOKEN=Atzr|xxxxx
AMAZON_REGION=NA
```

### Регионы

| Значение | Endpoint |
|----------|----------|
| `NA` | `https://advertising-api.amazon.com` |
| `EU` | `https://advertising-api-eu.amazon.com` |
| `FE` | `https://advertising-api-fe.amazon.com` |
| Sandbox | `https://advertising-api-test.amazon.com` |

---

## Основная концепция

Центральный объект — `AmazonAds`. Он регистрируется как **синглтон** в Laravel-контейнере. Перед каждым запросом нужно указать, от имени какого аккаунта работаем, — через `->authorize(AmazonCredentials $credentials)`.

```
AmazonAds
  ->authorize(AmazonCredentials)   ← переключает аккаунт
  ->profiles()                     ← ресурс профилей
  ->marketingStreamSubscriptions() ← ресурс подписок Marketing Stream
  ->client()                       ← прямой доступ к HTTP-клиенту
```

---

## Быстрый старт

### Получить список профилей

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

Или через helper-функцию:

```php
$profiles = amazon_ads()
    ->authorize($credentials)
    ->profiles()
    ->list();
```

---

## AmazonCredentials

DTO с данными одного рекламного аккаунта. Три способа создания:

### 1. Через RegionEnum (рекомендуется)

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

### 2. Через массив (удобно для данных из БД)

```php
// С регионом
$credentials = AmazonCredentials::fromArray([
    'client_id' => 'amzn1.application-oa2-client.xxxxx',
    'client_secret' => 'your-secret',
    'refresh_token' => 'Atzr|your-token',
    'region' => 'NA',
]);

// С кастомным endpoint напрямую
$credentials = AmazonCredentials::fromArray([
    'client_id' => 'amzn1.application-oa2-client.xxxxx',
    'client_secret' => 'your-secret',
    'refresh_token' => 'Atzr|your-token',
    'base_url' => 'https://advertising-api-eu.amazon.com',
]);

// Sandbox через fromArray
$credentials = AmazonCredentials::fromArray($account->toArray(), sandbox: true);
```

### 3. Через конструктор напрямую

```php
$credentials = new AmazonCredentials(
    clientId: 'amzn1.application-oa2-client.xxxxx',
    clientSecret: 'your-secret',
    refreshToken: 'Atzr|your-token',
    baseUrl: 'https://advertising-api.amazon.com',
    // tokenEndpoint: 'https://api.amazon.com/auth/o2/token', // опционально
);
```

---

## Агентский сценарий (multi-account)

Агентство управляет N рекламными аккаунтами. Credentials каждого клиента хранятся в БД и передаются динамически:

```php
use Jonston\AmazonAdsApi\AmazonAds;
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;

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

    public function createSubscription(Account $account, string $profileId, array $data): array
    {
        return $this->amazon
            ->authorize(AmazonCredentials::fromArray($account->amazon_credentials))
            ->marketingStreamSubscriptions($profileId)
            ->create($data);
    }
}
```

Переключение между аккаунтами в рамках одного запроса:

```php
$amazon = app(AmazonAds::class);

// Аккаунт A — NA
$amazon->authorize($credentialsA)->profiles()->list();

// Аккаунт B — EU
$amazon->authorize($credentialsB)->marketingStreamSubscriptions($profileId)->list();

// Снова аккаунт A
$amazon->authorize($credentialsA)->marketingStreamSubscriptions($profileId)->create([...]);
```

---

## Ресурсы

### ProfileResource

Доступ: `->profiles()`

| Метод | Описание |
|-------|----------|
| `list(): array` | Список всех профилей аккаунта |
| `get(string $profileId): array` | Получить профиль по ID |

```php
$amazon->authorize($credentials)->profiles()->list();
$amazon->authorize($credentials)->profiles()->get('1234567890');
```

### MarketingStreamSubscriptionResource

Доступ: `->marketingStreamSubscriptions(string $profileId)`

`$profileId` — это `Amazon-Advertising-API-Scope`, уникальный для каждого рекламного профиля.

| Метод | Описание |
|-------|----------|
| `list(array $params = []): array` | Список подписок (с фильтрами) |
| `get(string $id): array` | Получить подписку по ID |
| `create(array $data): array` | Создать подписку |
| `update(string $id, array $data): array` | Обновить подписку |
| `delete(string $id): array` | Удалить подписку |

```php
$resource = $amazon
    ->authorize($credentials)
    ->marketingStreamSubscriptions('1234567890');

// Список
$resource->list();
$resource->list(['destinationType' => 'SQS']);

// CRUD
$resource->get('sub-id-123');
$resource->create(['destinationType' => 'SQS', 'destinationArn' => 'arn:aws:sqs:...']);
$resource->update('sub-id-123', ['destinationArn' => 'arn:aws:sqs:...new']);
$resource->delete('sub-id-123');
```

Каждый вызов `->marketingStreamSubscriptions($profileId)` возвращает **независимый экземпляр** ресурса с собственным scope-заголовком — переключение профилей безопасно:

```php
// profile-aaa и profile-bbb не влияют друг на друга
$subA = $amazon->authorize($credentials)->marketingStreamSubscriptions('profile-aaa');
$subB = $amazon->authorize($credentials)->marketingStreamSubscriptions('profile-bbb');
```

---

## OAuth — обмен кода на токены

Если реализуете OAuth Authorization Code Flow (пользователь авторизует приложение через Amazon):

```php
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
// $tokens['refresh_token']  ← сохранить в БД
```

---

## Access Token

Пакет автоматически получает `access_token` через `refresh_token` перед каждым запросом и **кэширует его на 55 минут** (токен живёт 60 минут). Кэш использует стандартный Laravel Cache-драйвер.

Ключ кэша уникален для каждой пары `clientId + refreshToken`, поэтому разные аккаунты не конфликтуют.

---

## Прямые HTTP-запросы

Для эндпоинтов, которых ещё нет в виде ресурсов, используйте клиент напрямую:

```php
$result = amazon_ads()
    ->authorize($credentials)
    ->client()
    ->request('GET', '/v2/profiles');

// С заголовком scope
$result = amazon_ads()
    ->authorize($credentials)
    ->client()
    ->withHeaders(['Amazon-Advertising-API-Scope' => '1234567890'])
    ->request('GET', '/v2/campaigns', ['query' => ['stateFilter' => 'enabled']]);
```

`withHeaders()` иммутабелен — возвращает новый экземпляр клиента, оригинал не меняется.

---

## Обработка ошибок

Все исключения расширяют `AmazonApiException`:

```php
use Jonston\AmazonAdsApi\Exceptions\AmazonApiException;
use Illuminate\Http\Client\ConnectionException;

try {
    $profiles = amazon_ads()->authorize($credentials)->profiles()->list();
} catch (AmazonApiException $e) {
    // HTTP-ошибка API или OAuth-ошибка
    Log::error('Amazon API error', ['message' => $e->getMessage()]);
} catch (ConnectionException $e) {
    // Сетевая ошибка / таймаут
    Log::error('Amazon API connection failed', ['message' => $e->getMessage()]);
}
```

---

## Структура пакета

```
src/
├── AmazonAds.php                               ← точка входа, fluent, синглтон
├── AmazonAdsServiceProvider.php                ← регистрирует AmazonAds
├── AmazonClient.php                            ← HTTP-транспорт, иммутабельный
├── OAuthClient.php                             ← OAuth 2.0 токены
├── DTO/
│   └── AmazonCredentials.php                  ← credentials одного аккаунта
├── Enums/
│   └── RegionEnum.php                         ← хелпер для base URL
├── Exceptions/
│   └── AmazonApiException.php                 ← кастомные исключения
├── Resources/
│   ├── ProfileResource.php                    ← /v2/profiles
│   └── MarketingStreamSubscriptionResource.php ← /streams/subscriptions
└── helpers.php                                ← amazon_ads()
```

---

## Тестирование

```bash
composer test
```

или

```bash
./vendor/bin/phpunit
```

В тестах используйте `Http::fake()` для мока HTTP-запросов:

```php
use Illuminate\Support\Facades\Http;

Http::fake([
    'advertising-api.amazon.com/v2/profiles' => Http::response([
        ['profileId' => '123', 'countryCode' => 'US'],
    ], 200),
    'api.amazon.com/auth/o2/token' => Http::response([
        'access_token' => 'fake-token',
    ], 200),
]);

$profiles = amazon_ads()->authorize($credentials)->profiles()->list();
```

---

## Лицензия

MIT
