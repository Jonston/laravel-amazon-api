<?php

namespace Jonston\AmazonAdsApi;

use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Facades\Http;
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\Exceptions\AmazonApiException;

/**
 * Отвечает исключительно за OAuth 2.0 токены Amazon.
 */
final class OAuthClient
{
    public function __construct(
        private readonly AmazonCredentials $credentials,
    ) {
    }

    /**
     * Обменять authorization code на access/refresh tokens (Authorization Code Flow).
     */
    public function exchangeCode(string $code, string $redirectUri): array
    {
        return $this->post([
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $redirectUri,
            'client_id'     => $this->credentials->clientId,
            'client_secret' => $this->credentials->clientSecret,
        ]);
    }

    /**
     * Получить свежий access_token через refresh_token.
     */
    public function refreshAccessToken(): string
    {
        $data = $this->post([
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->credentials->clientId,
            'client_secret' => $this->credentials->clientSecret,
            'refresh_token' => $this->credentials->refreshToken,
        ]);

        return $data['access_token'];
    }

    // ---

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
