<?php

namespace Jonston\AmazonAdsApi\DTO;

/**
 * Payload for creating a Marketing Stream subscription.
 *
 * @see https://advertising.amazon.com/API/docs/en-us/reference/marketing-stream/subscriptions
 */
final readonly class CreateSubscriptionData
{
    /**
     * @param string      $dataSetId        Marketing Stream dataset identifier
     * @param string      $destinationType  Destination type (e.g. 'SQS')
     * @param string      $destinationArn   ARN of the destination resource
     * @param string|null $notes            Optional notes for the subscription
     */
    public function __construct(
        public string $dataSetId,
        public string $destinationType,
        public string $destinationArn,
        public ?string $notes = null,
    ) {}

    /**
     * Serialize to the API request payload.
     *
     * @return array
     */
    public function toArray(): array
    {
        $payload = [
            'dataSetId' => $this->dataSetId,
            'destinationType' => $this->destinationType,
            'destinationArn' => $this->destinationArn,
        ];

        if ($this->notes !== null) {
            $payload['notes'] = $this->notes;
        }

        return $payload;
    }
}

