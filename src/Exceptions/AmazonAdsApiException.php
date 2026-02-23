<?php

declare(strict_types=1);

namespace Jonston\AmazonAds\Exceptions;

class AmazonAdsApiException extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 0,
        public readonly array $responseBody = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}
