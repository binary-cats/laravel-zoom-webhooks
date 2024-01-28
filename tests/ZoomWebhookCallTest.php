<?php

namespace BinaryCats\ZoomWebhooks\Tests;

use BinaryCats\ZoomWebhooks\ProcessZoomWebhookJob;
use Illuminate\Support\Facades\Event;
use Spatie\WebhookClient\Models\WebhookCall;

class ZoomWebhookCallTest extends TestCase
{
    /** @var \BinaryCats\ZoomWebhooks\ProcessZoomWebhookJob */
    public $processZoomWebhookJob;

    /** @var \Spatie\WebhookClient\Models\WebhookCall */
    public $webhookCall;

    /** @return void */
    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        config(['zoom-webhooks.jobs' => ['my_type' => DummyJob::class]]);

        $this->webhookCall = WebhookCall::create([
            'name' => 'zoom',
            'payload' => [
                'event' => 'my.type',
                'payload' => [
                    'key' => 'value',
                ],
            ],
            'url' => '/webhooks/zoom.com',
        ]);

        $this->processZoomWebhookJob = new ProcessZoomWebhookJob($this->webhookCall);
    }

    /** @test */
    public function it_will_fire_off_the_configured_job()
    {
        $this->processZoomWebhookJob->handle();

        $this->assertEquals($this->webhookCall->id, cache('dummyjob')->id);
    }

    /** @test */
    public function it_will_not_dispatch_a_job_for_another_type()
    {
        config(['zoom-webhooks.jobs' => ['another_type' => DummyJob::class]]);

        $this->processZoomWebhookJob->handle();

        $this->assertNull(cache('dummyjob'));
    }

    /** @test */
    public function it_will_not_dispatch_jobs_when_no_jobs_are_configured()
    {
        config(['zoom-webhooks.jobs' => []]);

        $this->processZoomWebhookJob->handle();

        $this->assertNull(cache('dummyjob'));
    }

    /** @test */
    public function it_will_dispatch_events_even_when_no_corresponding_job_is_configured()
    {
        config(['zoom-webhooks.jobs' => ['another_type' => DummyJob::class]]);

        $this->processZoomWebhookJob->handle();

        $webhookCall = $this->webhookCall;

        Event::assertDispatched("zoom-webhooks::{$webhookCall->payload['event']}", function ($event, $eventPayload) use ($webhookCall) {
            $this->assertInstanceOf(WebhookCall::class, $eventPayload);
            $this->assertEquals($webhookCall->id, $eventPayload->id);

            return true;
        });

        $this->assertNull(cache('dummyjob'));
    }
}
