<?php

declare(strict_types=1);

namespace Jonston\AmazonAds\Resources;

final class Profiles extends AbstractResource
{
    public function list(): array
    {
        return $this->client->get($this->credentials, '/v2/profiles');
    }

    public function get(string $profileId): array
    {
        return $this->client->get($this->credentials, "/v2/profiles/{$profileId}");
    }

    public function update(string $profileId, array $data): array
    {
        return $this->client->put($this->credentials, "/v2/profiles/{$profileId}", $data);
    }
}
