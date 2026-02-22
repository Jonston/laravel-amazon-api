<?php

namespace Jonston\AmazonAdsApi;

use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Facades\Http;
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\Exceptions\AmazonApiException;

/**
 * Handles Amazon OAuth 2.0 token operations.
 */
final readonly class OAuthClient
{
    public function __construct(
        private AmazonCredentials $credentials,
    ) {}

    /**
     * Exchange an authorization code for access and refresh tokens.
     *
     * @param string $code        Authorization code from Amazon callback
     * @param string $redirectUri Redirect URI registered with the application
     * @return array{access_token: string, refresh_token: string}
     * @throws AmazonApiException
     */
    public function exchangeCode(string $code, string $redirectUri): array
    {
        return $this->post([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $this->credentials->clientId,
            'client_secret' => $this->credentials->clientSecret,
        ]);
    }

    /**
     * Obtain a fresh access token using the stored refresh token.
     *
     * @return string
     * @throws AmazonApiException
     */
    public function refreshAccessToken(): string
    {
        $data = $this->post([
            'grant_type' => 'refresh_token',
            'client_id' => $this->credentials->clientId,
            'client_secret' => $this->credentials->clientSecret,
            'refresh_token' => $this->credentials->refreshToken,
        ]);

        return $data['access_token'];
    }

    /**
     * @throws AmazonApiException
     */
    private function post(array $params): array
    {
        try {
            $response = Http::asForm()->post($this->credentials->tokenEndpoint, $params);

            if ($response->failed()) {
                throw AmazonApiException::oauthFailed($response->body());
            }

            return $response->json();
        } catch (HttpClientException $e) {
            throw AmazonApiException::oauthFailed($e->getMessage(), $e);
        }
    }
}
