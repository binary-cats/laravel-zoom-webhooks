<?php

namespace BinaryCats\ZoomWebhooks\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Fluent;
use Spatie\WebhookClient\Models\WebhookCall;

class HandleMeetingStarted
{
    use Dispatchable, SerializesModels;

    /**
     * Create new Handle Meeting Started job.
     *
     * @param  \Spatie\WebhookClient\Models\WebhookCall  $webhookCall
     */
    public function __construct(
        protected WebhookCall $webhookCall
    ) {
    }

    /**
     * Execute the Handle Meeting Started job.
     *
     * @return void
     */
    public function handle()
    {
        // $this->webhookCall->payload contains the complete request payload sent from Zoom
        // Base Spatie\WebhookClient\Models\WebhookCall model will read it into an array
        //
        // It is easy to confuse it with the "payload" field within the request payload.
        // Don't confuse them.
        /*
        [
            'event' => 'meeting.updated',       # name of the event
            'payload' => [
                'account_id' => 'abcdefg',      # your Zoom account ID
                ...
                'object' => [
                    'id' => 12345               # ID of the object for event, in our case - Zoom Meeting ID
                    ...
                ],
                'old_object' => [
                    ...                         # object changes delta
                ],
                'time_stamp' => 1612046179414,  # UNIX timestamp of change
            ],
            'event_ts' =>  1612046179414        # UNIX timestamp of event
        ]
        */
        // It is hard to come up with a uniform Model that will handle differnt sttructures
        // so what I usually do is create an abstract Job that will handle them; providing
        // with a paylod() method, as defined below.
        //
        // If you never worked with Fluent, try it, I promise you, you will love it.
    }

    /**
     * @return \Illuminate\Support\Fluent
     */
    protected function payload(): Fluent
    {
        return new Fluent($this->webhookCall->payload['payload'] ?? []);
    }
}
