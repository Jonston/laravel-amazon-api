<?php

declare(strict_types=1);

namespace Jonston\AmazonAds\Support;

use Jonston\AmazonAds\Auth\Credentials;
use Jonston\AmazonAds\Contracts\Resource;
use Jonston\AmazonAds\Http\HttpClient;

final readonly class ResourceFactory
{
    public function __construct(
        private HttpClient $client,
        private Credentials $credentials,
    ) {}

    /**
     * @template T of Resource
     * @param class-string<T> $resourceClass
     * @return T
     */
    public function make(string $resourceClass): Resource
    {
        return new $resourceClass($this->client, $this->credentials);
    }
}
