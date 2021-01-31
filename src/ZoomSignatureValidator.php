<?php

namespace BinaryCats\ZoomWebhooks;

use Exception;
use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class ZoomSignatureValidator implements SignatureValidator
{
    /**
     * Bind the implemetation.
     *
     * @var Illuminate\Http\Request
     */
    protected $request;

    /**
     * Inject the config.
     *
     * @var Spatie\WebhookClient\WebhookConfig
     */
    protected $config;

    /**
     * True if the signature has been valiates.
     *
     * @param  Illuminate\Http\Request       $request
     * @param  Spatie\WebhookClient\WebhookConfig $config
     *
     * @return bool
     */
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        $authorization = $request->header('authorization');
        $secret = $config->signingSecret;

        try {
            Webhook::constructEvent($request->all(), $authorization, $secret);
        } catch (Exception $exception) {
            report($exception);

            return false;
        }

        return true;
    }
}
