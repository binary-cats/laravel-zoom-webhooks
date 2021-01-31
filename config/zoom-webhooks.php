<?php

return [

    /*
     * Zoom will sign each webhook using a verification token. You can find the secret used
     * in the  page of your Marketplace app: .
     */
    'signing_secret' => env('ZOOM_WEBHOOK_SECRET'),

    /*
     * You can define the job that should be run when a certain webhook hits your application
     * here. The key is the name of the Zoom event type with the `.` replaced by a `_`.
     *
     * You can find a list of Zoom webhook types here:
     * https://marketplace.zoom.us/docs/api-reference/webhook-reference#events.
     */
    'jobs' => [
        // 'meeting_started' => \BinaryCats\ZoomWebhooks\Jobs\HandleMeetingStarted::class,
    ],

    /*
     * The classname of the model to be used. The class should equal or extend
     * Spatie\WebhookClient\Models\WebhookCall
     */
    'model' => \Spatie\WebhookClient\Models\WebhookCall::class,

    /*
     * The classname of the model to be used. The class should equal or extend
     * BinaryCats\ZoomWebhooks\ProcessZoomWebhookJob
     */
    'process_webhook_job' => \BinaryCats\ZoomWebhooks\ProcessZoomWebhookJob::class,
];
