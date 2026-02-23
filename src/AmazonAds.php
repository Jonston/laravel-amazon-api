<?php

declare(strict_types=1);

namespace Jonston\AmazonAds;

use Jonston\AmazonAds\Auth\Credentials;
use Jonston\AmazonAds\Http\HttpClient;

/**
 * Stateless entry point. Safe to use as a singleton in Swoole.
 * All state lives in Credentials and per-request AuthorizedClient instances.
 *
 * Usage:
 *   $amazon->authorize($credentials)->profiles()->list();
 *   $amazon->authorize($credentials)->marketingStreamSubscriptions($profileId)->create($data);
 */
final readonly class AmazonAds
{
    public function __construct(
        private HttpClient $httpClient,
    ) {}

    /**
     * Returns an immutable context bound to the given credentials.
     * You can call this multiple times with different credentials in the same request
     * without any state leaking between them.
     */
    public function authorize(Credentials $credentials): AuthorizedClient
    {
        return new AuthorizedClient($this->httpClient, $credentials);
    }
}
