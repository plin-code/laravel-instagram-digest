<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use PlinCode\InstagramDigest\Facades\InstagramDigest;
use PlinCode\InstagramDigest\Models\Profile;

beforeEach(function () {
    config()->set('instagram-digest.apify.token', 't');
    config()->set('instagram-digest.apify.actor_id', 'a');
    config()->set('instagram-digest.apify.timeout_seconds', 5);

    InstagramDigest::hashtagsUsing(fn () => ['trekking']);
    InstagramDigest::keywordsUsing(fn () => ['trek']);
    InstagramDigest::minFollowersUsing(fn () => 100);
});

it('--sync runs inline and actually persists profiles', function () {
    Http::fake([
        'api.apify.com/v2/acts/*/runs*' => Http::response(['data' => ['id' => 'R']], 201),
        'api.apify.com/v2/actor-runs/R' => Http::response(['data' => ['status' => 'SUCCEEDED']], 200),
        'api.apify.com/v2/actor-runs/R/dataset/items*' => Http::response([
            ['ownerUsername' => 'sync_u', 'ownerBio' => 'trekking', 'ownerFollowersCount' => 500],
        ], 200),
    ]);

    $this->artisan('instagram-digest:scrape', ['--sync' => true])->assertExitCode(0);

    expect(Profile::where('instagram_username', 'sync_u')->exists())->toBeTrue();
});
