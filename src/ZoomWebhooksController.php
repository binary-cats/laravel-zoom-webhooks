<?php

namespace BinaryCats\ZoomWebhooks;

use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProcessor;
use Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile;

class ZoomWebhooksController
{
    /**
     * Invoke controller method.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $configKey
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Spatie\WebhookClient\Exceptions\InvalidConfig
     */
    public function __invoke(Request $request, string $configKey = null)
    {
        $webhookConfig = new WebhookConfig([
            'name' => 'zoom',
            'signing_secret' => ($configKey) ?
                config('zoom-webhooks.signing_secret_'.$configKey) :
                config('zoom-webhooks.signing_secret'),
            'signature_header_name' => null,
            'signature_validator' => ZoomSignatureValidator::class,
            'webhook_profile' => ProcessEverythingWebhookProfile::class,
            'webhook_model' => config('zoom-webhooks.model'),
            'process_webhook_job' => config('zoom-webhooks.process_webhook_job'),
        ]);

        (new WebhookProcessor($request, $webhookConfig))->process();

        return response()->json(['message' => 'ok']);
    }
}
