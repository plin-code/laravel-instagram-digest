# laravel-instagram-digest

[![Latest Version on Packagist](https://img.shields.io/packagist/v/plin-code/laravel-instagram-digest.svg?style=flat-square)](https://packagist.org/packages/plin-code/laravel-instagram-digest)
[![Total Downloads](https://img.shields.io/packagist/dt/plin-code/laravel-instagram-digest.svg?style=flat-square)](https://packagist.org/packages/plin-code/laravel-instagram-digest)

Scrape Instagram hashtags via Apify, filter profiles by keywords and follower threshold, and send a daily Telegram digest with inline action buttons. Classify candidates with one tap.

## What it does

1. Runs the Apify `apify~instagram-scraper` actor against a list of hashtags.
2. Filters results by bio/username keyword match and a minimum follower count.
3. Upserts surviving profiles into `instagram_digest_profiles`.
4. Once a day, sends the next `N` pending profiles as Telegram cards with inline buttons: **Interesting**, **Reject**, **Show again later**. Custom actions pluggable.
5. Handles the callback when you tap a button: updates the profile status and removes the buttons from the message.

Bring your own data sources (hashtags, keywords, min-followers, chat id) via closures or plain config. Extend with custom action buttons and a custom card renderer.

## Installation

```bash
composer require plin-code/laravel-instagram-digest
php artisan migrate
```

Add to your `.env`:

```env
APIFY_TOKEN=your-apify-token
APIFY_ACTOR_ID=apify~instagram-scraper
APIFY_RESULTS_PER_HASHTAG=30

TELEGRAM_BOT_TOKEN=123:abc
TELEGRAM_CHAT_ID=-1001234567890
TELEGRAM_WEBHOOK_SECRET=a-long-random-string
```

## Quickstart

In `AppServiceProvider@boot`:

```php
use PlinCode\InstagramDigest\Facades\InstagramDigest;

public function boot(): void
{
    InstagramDigest::hashtagsUsing(fn () => ['trekking', 'hiking', 'guidealpine']);
    InstagramDigest::keywordsUsing(fn () => ['guida', 'trek', 'outdoor']);
    InstagramDigest::minFollowersUsing(fn () => 5000);
}
```

Register the Telegram webhook:

```bash
php artisan instagram-digest:webhook
```

Verify your Telegram setup end-to-end:

```bash
php artisan instagram-digest:demo
```

The demo uses a `placehold.co` URL for the placeholder image, so Telegram must be able to fetch that URL. If your network or bot configuration blocks external image fetches, pass a photo URL explicitly:

```bash
php artisan instagram-digest:demo --to=CHAT_ID
```

(Note: the `--to` option overrides the configured `chat_id` but currently uses the same placeholder image. For a full dry-run with your own image, register a custom `CardRenderer` — see below.)

## Data sources: resolvers vs config

Every data source has two equivalent ways to supply it.

**Via config** (`config/instagram-digest.php` or env):

```php
'hashtags' => ['trekking', 'hiking'],
'keywords' => ['guida', 'outdoor'],
'min_followers' => 5000,
```

**Via resolver closure** (takes precedence when registered):

```php
InstagramDigest::hashtagsUsing(fn () => Hashtag::active()->pluck('name')->all());
InstagramDigest::keywordsUsing(fn () => Keyword::all()->pluck('term')->all());
InstagramDigest::minFollowersUsing(fn () => Setting::get('min_followers', 5000));
InstagramDigest::chatIdUsing(fn () => auth()->user()->telegram_chat_id);
InstagramDigest::dailyCountUsing(fn () => 10);
```

If no resolver is registered, the package falls back to config.

## Custom actions

Register your own inline button:

```php
use PlinCode\InstagramDigest\Facades\InstagramDigest;
use PlinCode\InstagramDigest\Models\Profile;

InstagramDigest::registerAction(
    key: 'archive',
    label: 'Archive',
    handler: fn (Profile $p) => $p->update(['status' => 'archived']),
);
```

Replace the default action set entirely:

```php
InstagramDigest::defaultActions([
    new MyYesAction,
    new MyNoAction,
]);
```

Any class implementing `PlinCode\InstagramDigest\Contracts\DigestAction` is accepted.

## Custom card rendering

**Option A: publish the Blade view and edit it**

```bash
php artisan vendor:publish --tag=instagram-digest-views
```

Then edit `resources/views/vendor/instagram-digest/card.blade.php`.

**Option B: register your own renderer**

```php
use PlinCode\InstagramDigest\Contracts\CardRenderer;
use PlinCode\InstagramDigest\Facades\InstagramDigest;

InstagramDigest::renderCardUsing(MyCardRenderer::class);
```

Your renderer must return a `PlinCode\InstagramDigest\Support\CardPayload`.

## Customizing the webhook route

The webhook is registered by the package at `POST /instagram-digest/webhook/{secret?}` with the `api` middleware group. Both the URL prefix and the middleware stack are config-driven — edit `config/instagram-digest.php` after publishing:

```bash
php artisan vendor:publish --tag=instagram-digest-config
```

Then adjust:

```php
'route' => [
    'prefix' => 'instagram-digest',           // appears in the URL: /{prefix}/webhook/{secret?}
    'middleware' => ['api'],                  // any middleware array — e.g. ['api', 'throttle:60,1']
],
```

If you need full control (different HTTP verb, route model binding, custom controller), you can bypass the auto-registered route by setting `'middleware' => ['api', 'should-never-match']` (breaks the route) and defining your own pointing at `PlinCode\InstagramDigest\Http\Controllers\WebhookController`.

## Scheduling

The package does NOT register any scheduled tasks. Wire the commands yourself in `routes/console.php`:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('instagram-digest:scrape')->weekdays()->at('09:30');
Schedule::command('instagram-digest:send')->weekdays()->at('10:00');
```

## Events

Listen to the following events to integrate with your own domain:

| Event | Payload | Use case |
|---|---|---|
| `ProfileDiscovered` | `Profile $profile, bool $isNew` | Sync to your CRM / lead model — `$isNew` distinguishes first-time discovery from refresh |
| `ProfileStatusChanged` | `Profile $profile, string $from, string $to` | React to user classification |
| `DigestSent` | `array $profileIds` | Metrics, auditing |
| `ScrapingRunCompleted` | `Run $run` | Notifications |

Example listener:

```php
public function handle(ProfileDiscovered $event): void
{
    if (! $event->isNew) {
        return;
    }

    Prospect::firstOrCreate(
        ['instagram_handle' => $event->profile->instagram_username],
        ['status' => 'new'],
    );
}
```

## Testing your integration

The package plays nicely with Laravel's HTTP fakes and event fakes. In your own tests:

```php
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use PlinCode\InstagramDigest\Events\ProfileDiscovered;
use PlinCode\InstagramDigest\Jobs\RunHashtagScrapingJob;

it('my app reacts to ProfileDiscovered', function () {
    Event::fake([ProfileDiscovered::class]);
    Http::fake([
        'api.apify.com/*' => Http::response([/* ... */], 200),
    ]);

    dispatch_sync(new RunHashtagScrapingJob);

    Event::assertDispatched(ProfileDiscovered::class);
});
```

For the Telegram side, fake `api.telegram.org/*` and assert via `Http::assertSent(...)`.

## Commands

| Command | Description |
|---|---|
| `instagram-digest:scrape [--sync]` | Dispatch the Apify scraping job. |
| `instagram-digest:send [--count=N]` | Dispatch the Telegram digest job. |
| `instagram-digest:webhook [url?]` | Register the Telegram webhook with Telegram. |
| `instagram-digest:demo [--to=id]` | Send one fake card to verify Telegram config. |

## Testing

```bash
composer test
composer analyse
composer format
```

## License

MIT. See [LICENSE.md](LICENSE.md).
