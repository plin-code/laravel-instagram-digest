<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use PlinCode\InstagramDigest\Events\ProfileDiscovered;
use PlinCode\InstagramDigest\Models\Profile;
use PlinCode\InstagramDigest\Services\Apify\HashtagScraper;

beforeEach(function () {
    config()->set('instagram-digest.apify.token', 't');
    config()->set('instagram-digest.apify.actor_id', 'apify~instagram-scraper');
    config()->set('instagram-digest.apify.timeout_seconds', 5);
});

it('scrapes a hashtag, applies filters, upserts profiles, emits events', function () {
    Event::fake([ProfileDiscovered::class]);

    Http::fake([
        'api.apify.com/v2/acts/*/runs*' => Http::response(['data' => ['id' => 'R']], 201),
        'api.apify.com/v2/actor-runs/R' => Http::response(['data' => ['status' => 'SUCCEEDED']], 200),
        'api.apify.com/v2/actor-runs/R/dataset/items*' => Http::response([
            ['ownerUsername' => 'alice', 'ownerFullName' => 'Alice', 'ownerBio' => 'I love trekking', 'ownerFollowersCount' => 10000, 'ownerId' => 'IG1'],
            ['ownerUsername' => 'bob',   'ownerFullName' => 'Bob',   'ownerBio' => 'photographer',    'ownerFollowersCount' => 10000, 'ownerId' => 'IG2'],
            ['ownerUsername' => 'carol', 'ownerFullName' => 'Carol', 'ownerBio' => 'trekking fan',    'ownerFollowersCount' => 100,   'ownerId' => 'IG3'],
        ], 200),
    ]);

    $scraper = app(HashtagScraper::class);
    $result = $scraper->scrape(['trekking'], keywords: ['trekking'], minFollowers: 5000);

    expect(Profile::count())->toBe(1)
        ->and(Profile::first()->instagram_username)->toBe('alice')
        ->and($result['found'])->toBe(3)
        ->and($result['added'])->toBe(1);

    Event::assertDispatchedTimes(ProfileDiscovered::class, 1);
});
