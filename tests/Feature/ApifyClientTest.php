<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use PlinCode\InstagramDigest\Services\Apify\ApifyClient;

beforeEach(function () {
    config()->set('instagram-digest.apify.token', 'fake-token');
    config()->set('instagram-digest.apify.actor_id', 'apify~instagram-scraper');
    config()->set('instagram-digest.apify.timeout_seconds', 10);
});

it('starts a run and polls until SUCCEEDED, returning dataset items', function () {
    Http::fake([
        'api.apify.com/v2/acts/*/runs*' => Http::response(['data' => ['id' => 'RUN123']], 201),
        'api.apify.com/v2/actor-runs/RUN123' => Http::sequence()
            ->push(['data' => ['status' => 'RUNNING']], 200)
            ->push(['data' => ['status' => 'SUCCEEDED']], 200),
        'api.apify.com/v2/actor-runs/RUN123/dataset/items*' => Http::response([
            ['ownerUsername' => 'alice', 'ownerFullName' => 'Alice'],
            ['ownerUsername' => 'bob', 'ownerFullName' => 'Bob'],
        ], 200),
    ]);

    $client = new ApifyClient;
    $items = $client->runAndFetch(['trekking'], resultsLimit: 10);

    expect($items)->toHaveCount(2)
        ->and($items[0]['ownerUsername'])->toBe('alice');
});

it('throws on FAILED terminal status', function () {
    Http::fake([
        'api.apify.com/v2/acts/*/runs*' => Http::response(['data' => ['id' => 'R2']], 201),
        'api.apify.com/v2/actor-runs/R2' => Http::response(['data' => ['status' => 'FAILED']], 200),
    ]);

    $client = new ApifyClient;
    $client->runAndFetch(['trekking'], resultsLimit: 10);
})->throws(RuntimeException::class, 'Apify run failed');
