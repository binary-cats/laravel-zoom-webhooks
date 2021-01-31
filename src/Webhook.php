<?php

namespace BinaryCats\ZoomWebhooks;

use BinaryCats\ZoomWebhooks\Exceptions\UnexpectedValueException;

class Webhook
{
    /**
     * Validate and raise an appropriate event.
     *
     * @param  $payload
     * @param  string $signature
     * @param  string $secret
     * @return BinaryCats\ZoomWebhooks\Event
     */
    public static function constructEvent(array $payload, string $signature, string $secret): Event
    {
        // verify we are good, else throw an expection
        tap(WebhookSignature::make($signature, $secret), function ($signature) {
            if (! $signature->verify()) {
                throw new UnexpectedValueException('Failed to verify signature', 500);
            }
        });
        // Make an event
        return Event::constructFrom($payload);
    }
}
