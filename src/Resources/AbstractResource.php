<?php

declare(strict_types=1);

namespace Jonston\AmazonAds\Resources;

use Jonston\AmazonAds\Auth\Credentials;
use Jonston\AmazonAds\Http\HttpClient;

abstract class AbstractResource
{
    public function __construct(
        protected readonly HttpClient $client,
        protected readonly Credentials $credentials,
    ) {}

    protected function scopeHeaders(string $profileId): array
    {
        return ['Amazon-Advertising-API-Scope' => $profileId];
    }
}
