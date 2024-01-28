<?php

namespace BinaryCats\ZoomWebhooks;

class WebhookSignature
{
    /**
     * Create new Signature.
     *
     * @param  string  $signature  Authentication signature
     * @param  string  $secret  Signature secret
     */
    public function __construct(
        protected string $signature,
        protected string $secret
    ) {
    }

    /**
     * Static accessor into the class constructor.
     *
     * @param  string  $signature
     * @param  string  $secret
     * @return WebhookSignature static
     */
    public static function make(string $signature, string $secret)
    {
        return new static($signature, $secret);
    }

    /**
     * True if the signature is valid.
     *
     * @return bool
     */
    public function verify(): bool
    {
        return hash_equals($this->signature, $this->secret);
    }
}
