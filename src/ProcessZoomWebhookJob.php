<?php

namespace BinaryCats\ZoomWebhooks;

use BinaryCats\ZoomWebhooks\Exceptions\WebhookFailed;
use Illuminate\Support\Arr;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class ProcessZoomWebhookJob extends ProcessWebhookJob
{
    /**
     * Name of the payload key to contain the type of event.
     *
     * @var string
     */
    protected string $key = 'payload.event';

    /**
     * Handle the process.
     *
     * @return void
     */
    public function handle()
    {
        $type = Arr::get($this->webhookCall, $this->key);

        throw_if(! $type, WebhookFailed::missingType($this->webhookCall));

        event("zoom-webhooks::{$type}", $this->webhookCall);

        $jobClass = $this->determineJobClass($type);

        if ($jobClass === '') {
            return;
        }

        throw_if(! class_exists($jobClass), WebhookFailed::jobClassDoesNotExist($jobClass, $this->webhookCall));

        dispatch(new $jobClass($this->webhookCall));
    }

    /**
     * Calculate the class name.
     *
     * @param  string  $eventType
     * @return string
     */
    protected function determineJobClass(string $eventType): string
    {
        $jobConfigKey = str_replace('.', '_', $eventType);

        return config("zoom-webhooks.jobs.{$jobConfigKey}", '');
    }
}
