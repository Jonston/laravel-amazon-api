<?php

declare(strict_types=1);

namespace Jonston\AmazonAds\Resources;

use Jonston\AmazonAds\Auth\Credentials;
use Jonston\AmazonAds\Http\HttpClient;

final class MarketingStreamSubscriptions extends AbstractResource
{
    public function __construct(
        HttpClient $client,
        Credentials $credentials,
        private readonly string $profileId,
    ) {
        parent::__construct($client, $credentials);
    }

    public function list(): array
    {
        return $this->client->get($this->credentials, '/streams/subscriptions', headers: $this->scopeHeaders($this->profileId));
    }

    public function get(string $subscriptionId): array
    {
        return $this->client->get($this->credentials, "/streams/subscriptions/{$subscriptionId}", headers: $this->scopeHeaders($this->profileId));
    }

    public function create(array $data): array
    {
        return $this->client->post($this->credentials, '/streams/subscriptions', $data, $this->scopeHeaders($this->profileId));
    }

    public function update(string $subscriptionId, array $data): array
    {
        return $this->client->put($this->credentials, "/streams/subscriptions/{$subscriptionId}", $data, $this->scopeHeaders($this->profileId));
    }

    public function delete(string $subscriptionId): array
    {
        return $this->client->delete($this->credentials, "/streams/subscriptions/{$subscriptionId}", $this->scopeHeaders($this->profileId));
    }
}
