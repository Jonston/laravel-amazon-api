<?php

namespace Jonston\AmazonAdsApi\Exceptions;

use RuntimeException;

class AmazonApiException extends RuntimeException
{
    public static function accountNotFound(string $name): self
    {
        return new self("Amazon Ads account [{$name}] is not configured.");
    }

    public static function oauthFailed(string $reason, ?\Throwable $previous = null): self
    {
        return new self("Amazon OAuth request failed: {$reason}", 0, $previous);
    }

    public static function requestFailed(string $reason, ?\Throwable $previous = null): self
    {
        return new self("Amazon Ads API request failed: {$reason}", 0, $previous);
    }
}

