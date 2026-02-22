<?php

namespace Jonston\AmazonAdsApi;

use Jonston\AmazonAdsApi\Resources\MarketingStreamResource;
use Jonston\AmazonAdsApi\Resources\ProfileResource;

/**
 * Точка входа для работы с одним рекламным аккаунтом Amazon.
 *
 * Используется через AmazonManager:
 *   amazon_ads()->account('my-account')->profiles()->list()
 */
class AmazonAds
{
    public function __construct(
        private readonly AmazonClient $client,
    ) {
    }

    /**
     * Работа с профилями рекламного аккаунта.
     */
    public function profiles(): ProfileResource
    {
        return new ProfileResource($this->client);
    }

    /**
     * Работа с Marketing Stream подписками.
     *
     * @param string $profileId Amazon Advertising API Scope (profileId)
     */
    public function marketingStream(string $profileId): MarketingStreamResource
    {
        return new MarketingStreamResource($this->client, $profileId);
    }

    /**
     * Прямой доступ к клиенту (для нестандартных запросов).
     */
    public function client(): AmazonClient
    {
        return $this->client;
    }
}