<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use PlinCode\InstagramDigest\Actions\ReproposeAction;
use PlinCode\InstagramDigest\Facades\InstagramDigest;
use PlinCode\InstagramDigest\Jobs\SendDigestJob;
use PlinCode\InstagramDigest\Models\Profile;

beforeEach(function () {
    config()->set('instagram-digest.telegram.bot_token', 'BOT');
    InstagramDigest::chatIdUsing(fn () => 'CHAT');
    InstagramDigest::dailyCountUsing(fn () => 5);
});

it('Repropose puts a profile back into the next digest', function () {
    Http::fake([
        'api.telegram.org/*' => Http::sequence()
            ->push(['ok' => true, 'result' => ['message_id' => 1001]], 200)
            ->push(['ok' => true, 'result' => ['message_id' => 1002]], 200),
    ]);

    $profile = Profile::create([
        'instagram_username' => 'alice',
        'followers_count' => 10,
        'profile_pic_url' => 'https://x/y.jpg',
    ]);

    // First digest
    dispatch_sync(new SendDigestJob);
    expect($profile->fresh()->telegram_message_id)->toBe('1001')
        ->and($profile->fresh()->status)->toBe('pending');

    // Repropose
    (new ReproposeAction)->handle($profile->fresh());
    expect($profile->fresh()->telegram_message_id)->toBeNull()
        ->and($profile->fresh()->status)->toBe('pending');

    // Second digest picks the same profile up again
    dispatch_sync(new SendDigestJob);
    expect($profile->fresh()->telegram_message_id)->toBe('1002');
});
