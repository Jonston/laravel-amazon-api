<?php

declare(strict_types=1);

namespace Jonston\AmazonAds\Contracts;

interface Resource
{
    public function __construct(\Jonston\AmazonAds\Http\HttpClient $client, \Jonston\AmazonAds\Auth\Credentials $credentials);
}
