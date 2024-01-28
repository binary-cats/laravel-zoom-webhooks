<?php

namespace BinaryCats\ZoomWebhooks;

use BinaryCats\ZoomWebhooks\Exceptions\UnexpectedValueException;

class Webhook
{
    /**
     * Validate and raise an appropriate event.
     *
     * @param  array $payload
     * @param  string $signature
     * @param  string $secret
     * @return \BinaryCats\ZoomWebhooks\Event
     */
    public static function constructEvent(array $payload, string $signature, string $secret): Event
    {
        // verify we are good, else throw an exception
        tap(WebhookSignature::make($signature, $secret), function ($signature) {
            throw_unless(
                $signature->verify(),
                new UnexpectedValueException('Failed to verify signature', 500)
            );
        });
        // Make an event
        return Event::constructFrom($payload);
    }
}
