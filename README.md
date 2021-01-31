# Handle Zoom Webhooks in a Laravel application

![https://github.com/binary-cats/laravel-zoom-webhooks/actions](https://github.com/binary-cats/laravel-zoom-webhooks/workflows/Laravel/badge.svg)
![https://github.styleci.io/repos/](https://github.styleci.io/repos//shield)
![https://scrutinizer-ci.com/g/binary-cats/laravel-zoom-webhooks/](https://scrutinizer-ci.com/g/binary-cats/laravel-zoom-webhooks/badges/quality-score.png?b=master)

[Zoom](https://zoom.us) can notify your application of various events using webhooks. This package can help you handle those webhooks. Out of the box it will verify Zoom webhook signature of incoming requests. All valid calls will be logged to the database. You can easily define jobs or events that should be dispatched when specific events hit your app.

This package will not handle what should be done *after* the webhook request has been validated and the right job or event is called. You should still code up any work (eg. what should happen) yourself. For more, read on.

<p align="center"><img src="" width="400"></p>


Before using this package we highly recommend reading [the entire documentation on webhooks over at Zoom](https://marketplace.zoom.us/docs/api-reference/webhook-reference).

This package is an adaptation of absolutely amazing [spatie/laravel-stripe-webhooks](https://github.com/spatie/laravel-stripe-webhooks)

## Installation

You can install the package via composer:

```bash
composer require binary-cats/laravel-zoom-webhooks
```

The service provider will automatically register itself.

You must publish the config file with:
```bash
php artisan vendor:publish --provider="BinaryCats\ZoomWebhooks\ZoomWebhooksServiceProvider" --tag="config"
```

This is the contents of the config file that will be published at `config/zoom-webhooks.php`:

```php
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
```

In the `signing_secret` key of the config file you should add a valid webhook secret. You can find the secret used at [HTTP webhook signing key]().

**You can skip migrating is you have already installed `Spatie\WebhookClient`**

Next, you must publish the migration with:
```bash
php artisan vendor:publish --provider="Spatie\WebhookClient\WebhookClientServiceProvider" --tag="migrations"
```

After migration has been published you can create the `webhook_calls` table by running the migrations:

```bash
php artisan migrate
```

### Routing
Finally, take care of the routing: At [the Markerplace dashboard](https://marketplace.zoom.us/user/build) you must configure at what url Zoom webhooks should hit your app. In the routes file of your app you must pass that route to `Route::zoomWebhooks()`:

I *personally* like to group functionality by domain, so I would suggest `webhooks/zoom` (especially if you plan to have more webhooks), but it is your app, and it is up to you.

```php
# routes\web.php
Route::zoomWebhooks('webhooks/zoom');
```

Behind the scenes this will register a `POST` route to a controller provided by this package. Because Zoom has no way of getting a csrf-token, you must add that route to the `except` array of the `VerifyCsrfToken` middleware:

```php
protected $except = [
    'webhooks/zoom',
];
```

### Alternative middleware configuration

When you have multiple webhooks for various services, defined with a simialr packages, like [Stripe Webhooks](https://github.com/spatie/laravel-stripe-webhooks) and [Mailgun Webhooks](https://github.com/binary-cats/laravel-mailgun-webhooks) it may be easier to define `VerifyCsrfToken` middleware as:

```php
protected $except = [
    'webhooks/*',
];
```

## Usage

Zoom will send out webhooks for several event types. You can find the [full list of events types](https://marketplace.zoom.us/docs/api-reference/webhook-reference#events) in Zoom documentation.

Zoom will sign all requests hitting the webhook url of your app. This package will automatically verify if the signature is valid. If it is not, the request was probably not sent by Zoom.

Unless something goes terribly wrong, this package will always respond with a `200` to webhook requests. Sending a `200` will prevent Zoom from resending the same event over and over again. All webhook requests with a valid signature will be logged in the `webhook_calls` table. The table has a `payload` column where the entire payload of the incoming webhook is saved.

If the signature is not valid, the request will not be logged in the `webhook_calls` table but a `BinaryCats\ZoomWebhooks\Exceptions\WebhookFailed` exception will be thrown.
If something goes wrong during the webhook request the thrown exception will be saved in the `exception` column. In that case the controller will send a `500` instead of `200`.

There are two ways this package enables you to handle webhook requests: you can opt to queue a job or listen to the events the package will fire.

### Handling webhook requests using jobs

If you want to do something when a specific event type comes in you can define a job that does the work. Here's an example of such a job:

```php
<?php

namespace App\Jobs\ZoomWebhooks;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\WebhookClient\Models\WebhookCall;

class HandleMeetingStarted implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /** @var \Spatie\WebhookClient\Models\WebhookCall */
    public $webhookCall;

    public function __construct(WebhookCall $webhookCall)
    {
        $this->webhookCall = $webhookCall;
    }

    public function handle()
    {
        // do your work here

        // you can access the payload of the webhook call with `$this->webhookCall->payload`
    }
}
```

Spatie highly recommends that you make this job queueable to minimize the response time of the webhook requests. Asynchronous processing also allows you to handle more Zoom webhook requests and avoid timeouts. More on [queues](https://laravel.com/docs/8.x/queues).

Verification token is located in `authorization` reqeust header.

After having created your job you must register it at the `jobs` array in the `zoom-webhooks.php` config file. The **key** should be the name of [zoom event type](https://marketplace.zoom.us/docs/api-reference/webhook-reference#events) where but with the `.` replaced by `_`. The **value** should be the fully qualified class name.

```php
// config/zoom-webhooks.php

'jobs' => [
    'meeting_started' => \App\Jobs\ZoomWebhooks\HandleMeetingStarted::class,
],
```

### Handling webhook requests using events

Instead of queueing jobs to perform some work when a webhook request comes in, you can opt to listen to the events this package will fire. Whenever a valid request hits your app, the package will fire a `zoom-webhooks::<name-of-the-event>` event.

The payload of the events will be the instance of `WebhookCall` that was created for the incoming request.

Let's take a look at how you can listen for such an event. You can register your event listener in `EventServiceProvider`.

```php
/**
 * The event listener mappings for the application.
 *
 * @var array
 */
protected $listen = [
    'zoom-webhooks::meeting.started' => [
        App\Listeners\Zoom\MeetingStarted::class,
    ],
];
```

Here's an example of such a listener:

```php
<?php

namespace App\Listeners\Zoom;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\WebhookClient\Models\WebhookCall;

class MeetingStarted implements ShouldQueue
{
    public function handle(WebhookCall $webhookCall)
    {
        // do your work here

        // you can access the payload of the webhook call with `$webhookCall->payload`
    }
}
```

Spatie highly recommends that you make the event listener queueable, as this will minimize the response time of the webhook requests and allow you to consume more Zoom webhook requests while avoiding timeouts.

The above example is only one way to handle events in Laravel. To learn the other options, read [the Laravel documentation on handling events](https://laravel.com/docs/8.x/events).

## Advanced usage

### Retry handling a webhook

All incoming webhook requests are written to the database. This is incredibly valuable when something goes wrong while handling a webhook call. You can easily retry processing the webhook call, after you've investigated and fixed the cause of failure, like this:

```php
use Spatie\WebhookClient\Models\WebhookCall;
use BinaryCats\ZoomWebhooks\ProcessZoomWebhookJob;

dispatch(new ProcessZoomWebhookJob(WebhookCall::find($id)));
```

### Performing custom logic

You can add some custom logic that should be executed before and/or after the scheduling of the queued job by using your own job class. You can do this by specifying your own job class in the `process_webhook_job` key of the `zoom-webhooks` config file. The class should extend `BinaryCats\ZoomWebhooks\ProcessZoomWebhookJob`.

Here's an example:

```php
use BinaryCats\ZoomWebhooks\ProcessZoomWebhookJob;

class MyCustomZoomWebhookJob extends ProcessZoomWebhookJob
{
    public function handle()
    {
        // do some custom stuff beforehand

        parent::handle();

        // do some custom stuff afterwards
    }
}
```
### Handling multiple signing secrets

Sometimes you may want the package to handle multiple endpoints and secrets. Here's how to configurate that behaviour.

If you are using the `Route::zoomWebhooks` macro, you can append the `configKey` as follows:

```php
Route::zoomWebhooks('webhooks/zoom/{configKey}');
```

Alternatively, if you are manually defining the route, you can add `configKey` like so:

```php
Route::post('webhooks/zoom/{configKey}', 'BinaryCats\ZoomWebhooks\ZoomWebhooksController');
```

If this route parameter is present, the verify middleware will look for the secret using a different config key, by appending the given the parameter value to the default config key. E.g. if Zoom posts to `webhooks/zoom/my-special-secret` you'd need to add a new config named `signing_secret_my-special-secret`, like so:

```php
// secret for when Zoom posts to webhooks/zoom/account
'signing_secret_account' => 'whsec_abcdef',
// secret for when Zoom posts to webhooks/zoom/my-special-secret
'signing_secret_my-special-secret' => 'whsec_123456',
```

### About Zoom

[Zoom](https://www.zoom.us/) Zoom is a web-based video conferencing tool with a local, desktop client and a mobile app that allows users to meet online, with or without video.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information about what has changed recently.

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email cyrill.kalita@gmail.com instead of using issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

## Credits

- [Cyrill Kalita](https://github.com/binary-cats)
- [All Contributors](../../contributors)

Big shout-out to [Spatie](https://spatie.be/) for their work, which is a huge inspiration.

## Support us

Binary Cats is a web agency based in Illinois, US.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
