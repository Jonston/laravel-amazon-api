<?php

declare(strict_types=1);

namespace Jonston\AmazonAds\Auth;

final readonly class Credentials
{
    public function __construct(
        public string $clientId,
        public string $clientSecret,
        public string $refreshToken,
        public string $endpoint = 'https://advertising-api.amazon.com',
        public string $tokenEndpoint = 'https://api.amazon.com/auth/o2/token',
    ) {}

    public static function make(
        string $clientId,
        string $clientSecret,
        string $refreshToken,
        string $endpoint = 'https://advertising-api.amazon.com',
    ): self {
        return new self($clientId, $clientSecret, $refreshToken, $endpoint);
    }

    /**
     * Named constructors for known regions.
     */
    public static function northAmerica(string $clientId, string $clientSecret, string $refreshToken): self
    {
        return new self($clientId, $clientSecret, $refreshToken, 'https://advertising-api.amazon.com');
    }

    public static function europe(string $clientId, string $clientSecret, string $refreshToken): self
    {
        return new self($clientId, $clientSecret, $refreshToken, 'https://advertising-api-eu.amazon.com');
    }

    public static function farEast(string $clientId, string $clientSecret, string $refreshToken): self
    {
        return new self($clientId, $clientSecret, $refreshToken, 'https://advertising-api-fe.amazon.com');
    }
}
