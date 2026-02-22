<?php

namespace Jonston\AmazonAdsApi\Exceptions;

use RuntimeException;

/**
 * Thrown when an Amazon Ads API or OAuth request fails.
 */
class AmazonApiException extends RuntimeException
{
    /**
     * @param string $name
     * @return self
     */
    public static function accountNotFound(string $name): self
    {
        return new self("Amazon Ads account [{$name}] is not configured.");
    }

    /**
     * @param string          $reason
     * @param \Throwable|null $previous
     * @return self
     */
    public static function oauthFailed(string $reason, ?\Throwable $previous = null): self
    {
        return new self("Amazon OAuth request failed: {$reason}", 0, $previous);
    }


    /**
     * @param string          $reason
     * @param \Throwable|null $previous
     * @return self
     */
    public static function requestFailed(string $reason, ?\Throwable $previous = null): self
    {
        return new self("Amazon Ads API request failed: {$reason}", 0, $previous);
    }
}
