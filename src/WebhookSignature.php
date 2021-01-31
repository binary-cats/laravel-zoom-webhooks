<?php

namespace BinaryCats\ZoomWebhooks;

class WebhookSignature
{
    /**
     * AuthorixationAuthorixation signature.
     *
     * @var string
     */
    protected $signature;

    /**
     * Signature secret.
     *
     * @var string
     */
    protected $secret;

    /**
     * Create new Signature.
     *
     * @param string  $signature
     * @param string $secret
     */
    public function __construct(string $signature, string $secret)
    {
        $this->signature = $signature;
        $this->secret = $secret;
    }

    /**
     * Statis accessor into the class constructor.
     *
     * @param  string  $signature
     * @param  string $secret
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
