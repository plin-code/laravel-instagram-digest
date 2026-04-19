<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use PlinCode\InstagramDigest\Events\ScrapingRunCompleted;
use PlinCode\InstagramDigest\Facades\InstagramDigest;
use PlinCode\InstagramDigest\Jobs\RunHashtagScrapingJob;
use PlinCode\InstagramDigest\Models\Run;

beforeEach(function () {
    config()->set('instagram-digest.apify.token', 't');
    config()->set('instagram-digest.apify.actor_id', 'a');
    config()->set('instagram-digest.apify.timeout_seconds', 5);

    InstagramDigest::hashtagsUsing(fn () => ['trekking']);
    InstagramDigest::keywordsUsing(fn () => ['trek']);
    InstagramDigest::minFollowersUsing(fn () => 1000);
});

it('creates a Run, marks it succeeded, and emits ScrapingRunCompleted', function () {
    Event::fake([ScrapingRunCompleted::class]);

    Http::fake([
        'api.apify.com/v2/acts/*/runs*' => Http::response(['data' => ['id' => 'R']], 201),
        'api.apify.com/v2/actor-runs/R' => Http::response(['data' => ['status' => 'SUCCEEDED']], 200),
        'api.apify.com/v2/actor-runs/R/dataset/items*' => Http::response([
            ['ownerUsername' => 'u1', 'ownerBio' => 'trekking', 'ownerFollowersCount' => 2000],
        ], 200),
    ]);

    dispatch_sync(new RunHashtagScrapingJob);

    $run = Run::first();
    expect($run->status)->toBe('succeeded')
        ->and($run->profiles_found)->toBe(1)
        ->and($run->profiles_added)->toBe(1);

    Event::assertDispatched(ScrapingRunCompleted::class);
});

it('marks the run failed if the scraper throws', function () {
    Http::fake([
        'api.apify.com/v2/acts/*/runs*' => Http::response(['data' => ['id' => 'R']], 201),
        'api.apify.com/v2/actor-runs/R' => Http::response(['data' => ['status' => 'FAILED']], 200),
    ]);

    try {
        dispatch_sync(new RunHashtagScrapingJob);
    } catch (Throwable $e) {
        // expected
    }

    $run = Run::first();
    expect($run->status)->toBe('failed')
        ->and($run->error_message)->not->toBeEmpty();
});
