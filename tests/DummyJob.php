<?php

namespace BinaryCats\ZoomWebhooks\Tests;

use Spatie\WebhookClient\Models\WebhookCall;

class DummyJob
{
    public function __construct(
        public WebhookCall $webhookCall
    ) {
    }

    /**
     * Handle the Dummy job.
     */
    public function handle(): vpid
    {
        cache()->put('dummyjob', $this->webhookCall);
    }
}
