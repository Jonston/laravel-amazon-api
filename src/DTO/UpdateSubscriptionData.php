<?php

namespace Jonston\AmazonAdsApi\DTO;

/**
 * Payload for updating a Marketing Stream subscription.
 *
 * @see https://advertising.amazon.com/API/docs/en-us/reference/marketing-stream/subscriptions
 */
final readonly class UpdateSubscriptionData
{
    /**
     * @param string|null $destinationArn Updated ARN of the destination resource
     * @param string|null $notes          Updated notes for the subscription
     */
    public function __construct(
        public ?string $destinationArn = null,
        public ?string $notes = null,
    ) {}

    /**
     * Serialize to the API request payload (only non-null fields are included).
     *
     * @return array
     */
    public function toArray(): array
    {
        $payload = [];

        if ($this->destinationArn !== null) {
            $payload['destinationArn'] = $this->destinationArn;
        }

        if ($this->notes !== null) {
            $payload['notes'] = $this->notes;
        }

        return $payload;
    }
}

