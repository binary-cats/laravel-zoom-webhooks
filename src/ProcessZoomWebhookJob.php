<?php

namespace BinaryCats\ZoomWebhooks;

use BinaryCats\ZoomWebhooks\Exceptions\WebhookFailed;
use Illuminate\Support\Arr;
use Spatie\WebhookClient\ProcessWebhookJob;

class ProcessZoomWebhookJob extends ProcessWebhookJob
{
    /**
     * Name of the payload key to contain the type of event.
     *
     * @var string
     */
    protected $key = 'payload.event';

    /**
     * Handle the process.
     *
     * @return void
     */
    public function handle()
    {
        $type = Arr::get($this->webhookCall, $this->key);

        if (! $type) {
            throw WebhookFailed::missingType($this->webhookCall);
        }

        event("zoom-webhooks::{$type}", $this->webhookCall);

        $jobClass = $this->determineJobClass($type);

        if ($jobClass === '') {
            return;
        }

        if (! class_exists($jobClass)) {
            throw WebhookFailed::jobClassDoesNotExist($jobClass, $this->webhookCall);
        }

        dispatch(new $jobClass($this->webhookCall));
    }

    /**
     * Calculate the class name.
     *
     * @param  string $eventType
     * @return string
     */
    protected function determineJobClass(string $eventType): string
    {
        $jobConfigKey = str_replace('.', '_', $eventType);

        return config("zoom-webhooks.jobs.{$jobConfigKey}", '');
    }
}
