<?php

declare(strict_types=1);

namespace Jonston\AmazonAds\Auth;

use Illuminate\Support\Carbon;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Client\Factory as HttpFactory;
use Jonston\AmazonAds\Exceptions\AuthException;

final readonly class TokenResolver
{
    public function __construct(
        private Cache $cache,
        private HttpFactory $httpClient,
    ) {}

    public function resolve(Credentials $credentials): AccessToken
    {
        $cacheKey = $this->cacheKey($credentials);

        /** @var AccessToken|null $cached */
        $cached = $this->cache->get($cacheKey);

        if ($cached instanceof AccessToken && ! $cached->isExpired()) {
            return $cached;
        }

        return $this->refresh($credentials, $cacheKey);
    }

    private function refresh(Credentials $credentials, string $cacheKey): AccessToken
    {
        try {
            $response = $this->httpClient
                ->timeout(10)
                ->asForm()
                ->post($credentials->tokenEndpoint, [
                    'grant_type'    => 'refresh_token',
                    'client_id'     => $credentials->clientId,
                    'client_secret' => $credentials->clientSecret,
                    'refresh_token' => $credentials->refreshToken,
                ]);

            $response->throw();

            $data = $response->json();

            $token = new AccessToken(
                token: $data['access_token'],
                expiresAt: Carbon::now()->addSeconds($data['expires_in']),
            );

            $this->cache->put($cacheKey, $token, $data['expires_in'] - 60);

            return $token;
        } catch (\Throwable $e) {
            throw new AuthException(
                "Failed to refresh Amazon Ads access token: {$e->getMessage()}",
                previous: $e,
            );
        }
    }

    private function cacheKey(Credentials $credentials): string
    {
        return 'amazon_ads_token_' . md5($credentials->clientId . $credentials->refreshToken);
    }
}
