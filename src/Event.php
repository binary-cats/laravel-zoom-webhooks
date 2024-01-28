<?php

namespace BinaryCats\ZoomWebhooks;

use BinaryCats\ZoomWebhooks\Contracts\WebhookEvent;

class Event implements WebhookEvent
{
    /**
     * Create new Event.
     *
     * @param  array  $attributes  Attributes from the event
     */
    public function __construct(
        public array $attributes
    ) {
    }

    /**
     * Construct the event statically from array.
     *
     * @return static
     */
    public static function constructFrom($data): self
    {
        return new static($data);
    }
}
