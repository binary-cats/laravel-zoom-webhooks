<?php

namespace BinaryCats\ZoomWebhooks\Tests;

use Spatie\WebhookClient\Models\WebhookCall;

class DummyJob
{
    /**
     * Create new Dummy Job.
     *
     * @param  \Spatie\WebhookClient\Models\WebhookCall  $webhookCall
     */
    public function __construct(
        public WebhookCall $webhookCall
    ) {
    }

    /**
     * Handle the Dummy job.
     *
     * @return void
     */
    public function handle()
    {
        cache()->put('dummyjob', $this->webhookCall);
    }
}
