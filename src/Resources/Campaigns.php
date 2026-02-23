<?php

declare(strict_types=1);

namespace Jonston\AmazonAds\Resources;

use Jonston\AmazonAds\Auth\Credentials;
use Jonston\AmazonAds\Http\HttpClient;

final class Campaigns extends AbstractResource
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
        return $this->client->get($this->credentials, '/v2/campaigns', $query, $this->scopeHeaders($this->profileId));
    }

    public function get(string $campaignId): array
    {
        return $this->client->get($this->credentials, "/v2/campaigns/{$campaignId}", headers: $this->scopeHeaders($this->profileId));
    }

    public function create(array $data): array
    {
        return $this->client->post($this->credentials, '/v2/campaigns', $data, $this->scopeHeaders($this->profileId));
    }

    public function update(string $campaignId, array $data): array
    {
        return $this->client->put($this->credentials, "/v2/campaigns/{$campaignId}", $data, $this->scopeHeaders($this->profileId));
    }

    public function delete(string $campaignId): array
    {
        return $this->client->delete($this->credentials, "/v2/campaigns/{$campaignId}", $this->scopeHeaders($this->profileId));
    }
}
