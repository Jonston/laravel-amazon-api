<?php

namespace Jonston\AmazonAdsApi;

use Illuminate\Http\Client\HttpClientException;
use Illuminate\Support\Facades\Http;
use Jonston\AmazonAdsApi\DTO\AmazonCredentials;
use Jonston\AmazonAdsApi\Exceptions\AmazonApiException;

final class OAuthClient
{
    public function __construct(
        private readonly AmazonCredentials $credentials,
    ) {
    }

    /**
     * Обменять authorization code на access/refresh tokens.
     */
    public function exchangeCode(string $code, string $redirectUri): array
    {
        return $this->request([
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
    public function getAccessToken(): string
    {
        $data = $this->request([
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->credentials->clientId,
            'client_secret' => $this->credentials->clientSecret,
            'refresh_token' => $this->credentials->refreshToken,
        ]);

        return $data['access_token'];
    }

    private function request(array $params): array
    {
        try {
            $endpoint = $this->credentials->region->getTokenEndpoint();

            $response = Http::asForm()->post($endpoint, $params);

            if ($response->failed()) {
                throw AmazonApiException::oauthFailed($response->body());
            }

            return $response->json();
        } catch (HttpClientException $e) {
            throw AmazonApiException::oauthFailed($e->getMessage(), $e);
        }
    }
}
