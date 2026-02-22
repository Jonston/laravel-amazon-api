<?php

namespace Jonston\AmazonAdsApi\Contracts;

use Jonston\AmazonAdsApi\AmazonAds;
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;

interface AmazonManagerContract
{
    /**
     * Получить экземпляр AmazonAds по имени аккаунта.
     */
    public function account(string $name): AmazonAds;

    /**
     * Зарегистрировать аккаунт динамически.
     */
    public function addAccount(string $name, AmazonCredentials $credentials): self;

    /**
     * Получить список всех зарегистрированных аккаунтов.
     *
     * @return string[]
     */
    public function accountNames(): array;
}

