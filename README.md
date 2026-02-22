# Laravel Amazon SP-API

Laravel-пакет для интеграции с Amazon Selling Partner API (SP-API).

## Установка

```bash
composer require your-vendor/laravel-amazon-api
```

Публикация конфига:
```bash
php artisan vendor:publish --tag=amazon-api-config
```

## Переменные окружения

```dotenv
AMAZON_CLIENT_ID=your-client-id
AMAZON_CLIENT_SECRET=your-client-secret
AMAZON_REFRESH_TOKEN=your-refresh-token
AMAZON_MARKETPLACE_ID=ATVPDKIKX0DER
AMAZON_REGION=na
AMAZON_SANDBOX=false
```

## Использование

```php
use YourVendor\AmazonApi\Resources\Orders\OrdersResource;
use YourVendor\AmazonApi\Resources\Products\ProductsResource;
use YourVendor\AmazonApi\Resources\Inventory\InventoryResource;

// Через DI или app()
$orders = app(OrdersResource::class);

$order  = $orders->getOrder('123-4567890-1234567');
$items  = $orders->getOrderItems('123-4567890-1234567');

$products = app(ProductsResource::class);
$item     = $products->getCatalogItem('B08N5WRWNW', ['marketplaceIds' => ['ATVPDKIKX0DER']]);

$inventory = app(InventoryResource::class);
$summaries = $inventory->getInventorySummaries(['granularityType' => 'Marketplace', 'granularityId' => 'ATVPDKIKX0DER', 'marketplaceIds' => ['ATVPDKIKX0DER']]);
```

## Структура

```
src/
├── AmazonApiServiceProvider.php   # ServiceProvider: регистрация и публикация конфига
├── AmazonClient.php               # HTTP-клиент SP-API (Guzzle + Bearer token)
├── OAuthClient/
│   ├── LwaClient.php              # Получение LWA access token
│   └── TokenCache.php             # Кэширование токена через Laravel Cache
└── Resources/
    ├── AbstractResource.php       # Базовый класс ресурса
    ├── Orders/OrdersResource.php
    ├── Products/ProductsResource.php
    └── Inventory/InventoryResource.php
config/
└── amazon-api.php
```

