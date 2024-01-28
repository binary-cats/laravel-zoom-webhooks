<?php

namespace BinaryCats\ZoomWebhooks;

use Exception;
use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class ZoomSignatureValidator implements SignatureValidator
{
    /**
     * True if the signature has been validated.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Spatie\WebhookClient\WebhookConfig $config
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
