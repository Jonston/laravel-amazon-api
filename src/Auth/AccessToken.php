<?php

declare(strict_types=1);

namespace Jonston\AmazonAds\Auth;

use Illuminate\Support\Carbon;

final readonly class AccessToken
{
    public function __construct(
        public string $token,
        public Carbon $expiresAt,
    ) {}

    public function isExpired(): bool
    {
        return $this->expiresAt->subSeconds(60)->isPast();
    }
}
