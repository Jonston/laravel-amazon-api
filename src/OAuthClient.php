<?php

namespace Jonston\AmazonAdsApi;

use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

readonly class OAuthClient
{
    public function __construct(
        private string $clientId,
        private string $clientSecret,
        private string $tokenEndpoint,
    ) {
    }

    /**
     * Exchange an authorization code for access/refresh tokens.
     */
    public function exchangeCode(string $code, string $redirectUri): array
    {
        return $this->request([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);
    }

    /**
     * Request a fresh access token using a refresh token.
     */
    public function getAccessToken(string $refreshToken): string
    {
        $data = $this->request([
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
        ]);

        return $data['access_token'];
    }

    private function request(array $params): array
    {
        try {
            $response = Http::asForm()->post($this->tokenEndpoint, $params);

            if ($response->failed()) {
                throw new RuntimeException('OAuth request failed: ' . $response->body());
            }

            return $response->json();
        } catch (HttpClientException $e) {
            throw new RuntimeException('OAuth request failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
