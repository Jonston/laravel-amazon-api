<?php

namespace Jonston\AmazonAdsApi;

use Jonston\AmazonAdsApi\Contracts\AmazonManagerContract;
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\Exceptions\AmazonApiException;

/**
 * Менеджер рекламных аккаунтов Amazon.
 *
 * Агентство может иметь множество рекламных аккаунтов.
 * Каждый аккаунт регистрируется под уникальным именем и имеет свои credentials.
 *
 * Пример конфига:
 *   'accounts' => [
 *       'client-a' => ['client_id' => '...', 'client_secret' => '...', 'refresh_token' => '...', 'region' => 'NA'],
 *       'client-b' => ['client_id' => '...', 'client_secret' => '...', 'refresh_token' => '...', 'region' => 'EU'],
 *   ]
 *
 * Пример использования:
 *   app(AmazonManager::class)->account('client-a')->profiles()->list()
 *   amazon_ads('client-a')->profiles()->list()
 */
class AmazonManager implements AmazonManagerContract
{
    /** @var array<string, AmazonCredentials> */
    private array $credentials = [];

    /** @var array<string, AmazonAds> */
    private array $resolved = [];

    private bool $sandbox;

    public function __construct(array $config)
    {
        $this->sandbox = (bool) ($config['sandbox'] ?? false);

        foreach ($config['accounts'] ?? [] as $name => $accountConfig) {
            $this->credentials[$name] = AmazonCredentials::fromArray($accountConfig);
        }
    }

    /**
     * Получить AmazonAds для конкретного аккаунта.
     *
     * @throws AmazonApiException
     */
    public function account(string $name): AmazonAds
    {
        if (!isset($this->credentials[$name])) {
            throw AmazonApiException::accountNotFound($name);
        }

        // Каждый аккаунт резолвится один раз и кэшируется в памяти
        return $this->resolved[$name] ??= $this->makeAmazonAds($name);
    }

    /**
     * Динамически добавить аккаунт во время выполнения.
     * Полезно когда аккаунты хранятся в БД, а не в конфиге.
     */
    public function addAccount(string $name, AmazonCredentials $credentials): self
    {
        $this->credentials[$name] = $credentials;
        unset($this->resolved[$name]); // сбросить закэшированный инстанс если был

        return $this;
    }

    /**
     * Получить список всех зарегистрированных имён аккаунтов.
     *
     * @return string[]
     */
    public function accountNames(): array
    {
        return array_keys($this->credentials);
    }

    /**
     * Проверить, зарегистрирован ли аккаунт.
     */
    public function hasAccount(string $name): bool
    {
        return isset($this->credentials[$name]);
    }

    // ---

    private function makeAmazonAds(string $name): AmazonAds
    {
        $client = new AmazonClient($this->credentials[$name], $this->sandbox);

        return new AmazonAds($client);
    }
}

