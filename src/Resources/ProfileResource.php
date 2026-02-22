<?php

namespace Jonston\AmazonAdsApi\Resources;

use Illuminate\Http\Client\ConnectionException;
use Jonston\AmazonAdsApi\AmazonClient;
use Jonston\AmazonAdsApi\Exceptions\AmazonApiException;

class ProfileResource
{
    private const PATH = '/v2/profiles';

    public function __construct(private readonly AmazonClient $client)
    {
    }

    /**
     * Получить список всех профилей, доступных авторизованному пользователю.
     *
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function list(): array
    {
        return $this->client->request('GET', self::PATH);
    }

    /**
     * Получить профиль по ID.
     *
     * @throws AmazonApiException
     * @throws ConnectionException
     */
    public function get(string $profileId): array
    {
        return $this->client->request('GET', self::PATH . "/{$profileId}");
    }
}