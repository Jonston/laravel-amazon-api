<?php

namespace Jonston\AmazonAdsApi;

use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\Resources\MarketingStreamSubscriptionResource;
use Jonston\AmazonAdsApi\Resources\ProfileResource;

/**
 * Точка входа в Amazon Ads API.
 *
 * Регистрируется как синглтон в Laravel. Переключение между аккаунтами
 * агентства происходит через ->authorize() без создания нового объекта:
 *
 *   $amazon->authorize($credentialsA)->profiles()->list();
 *   $amazon->authorize($credentialsB)->marketingStreamSubscriptions($profileId)->create([...]);
 *
 * Для работы без Laravel — просто создайте экземпляр напрямую:
 *   $amazon = new AmazonAds();
 *   $amazon->authorize(AmazonCredentials::fromRegion(RegionEnum::NA, ...));
 */
class AmazonAds
{
    private ?AmazonClient $client = null;

    /**
     * Установить credentials для текущего запроса.
     * Возвращает $this для fluent-цепочки.
     */
    public function authorize(AmazonCredentials $credentials): static
    {
        $this->client = new AmazonClient($credentials);

        return $this;
    }

    /**
     * Ресурс профилей рекламного аккаунта.
     */
    public function profiles(): ProfileResource
    {
        return new ProfileResource($this->resolveClient());
    }

    /**
     * Ресурс Marketing Stream подписок для конкретного профиля.
     *
     * @param string $profileId Amazon Advertising API Scope (profileId)
     */
    public function marketingStreamSubscriptions(string $profileId): MarketingStreamSubscriptionResource
    {
        return new MarketingStreamSubscriptionResource($this->resolveClient(), $profileId);
    }

    /**
     * Прямой доступ к HTTP-клиенту для нестандартных запросов.
     */
    public function client(): AmazonClient
    {
        return $this->resolveClient();
    }

    // ---

    private function resolveClient(): AmazonClient
    {
        if ($this->client === null) {
            throw new \LogicException(
                'No credentials set. Call ->authorize(AmazonCredentials $credentials) first.'
            );
        }

        return $this->client;
    }
}