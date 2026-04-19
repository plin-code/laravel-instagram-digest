<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use PlinCode\InstagramDigest\Events\DigestSent;
use PlinCode\InstagramDigest\Facades\InstagramDigest;
use PlinCode\InstagramDigest\Jobs\SendDigestJob;
use PlinCode\InstagramDigest\Models\Profile;

beforeEach(function () {
    config()->set('instagram-digest.telegram.bot_token', 'BOT');
    InstagramDigest::chatIdUsing(fn () => 'CHAT');
    InstagramDigest::dailyCountUsing(fn () => 2);
});

it('sends N pending profiles and stores telegram_message_id', function () {
    Event::fake([DigestSent::class]);

    Http::fake([
        'api.telegram.org/*' => Http::sequence()
            ->push(['ok' => true, 'result' => ['message_id' => 100]], 200)
            ->push(['ok' => true, 'result' => ['message_id' => 101]], 200),
    ]);

    Profile::create(['instagram_username' => 'a', 'followers_count' => 1, 'profile_pic_url' => 'https://x/y.jpg']);
    Profile::create(['instagram_username' => 'b', 'followers_count' => 1, 'profile_pic_url' => 'https://x/z.jpg']);
    Profile::create(['instagram_username' => 'c', 'followers_count' => 1, 'profile_pic_url' => 'https://x/w.jpg']);

    dispatch_sync(new SendDigestJob);

    expect(Profile::whereNotNull('telegram_message_id')->count())->toBe(2);

    Event::assertDispatched(DigestSent::class);
});

it('does not re-send profiles that already have telegram_message_id', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]], 200)]);

    Profile::create(['instagram_username' => 'a', 'followers_count' => 1, 'telegram_message_id' => '99']);
    Profile::create(['instagram_username' => 'b', 'followers_count' => 1]);

    dispatch_sync(new SendDigestJob);

    expect(Profile::where('instagram_username', 'a')->first()->telegram_message_id)->toBe('99')
        ->and(Profile::where('instagram_username', 'b')->first()->telegram_message_id)->not->toBeNull();
});

it('logs and continues when a single sendPhoto call fails', function () {
    // First profile gets a 500 (no retry since it's not 429); second succeeds.
    Http::fake([
        'api.telegram.org/*' => Http::sequence()
            ->push(['error' => 'internal'], 500)
            ->push(['ok' => true, 'result' => ['message_id' => 200]], 200),
    ]);

    $a = Profile::create(['instagram_username' => 'bad',  'followers_count' => 1, 'profile_pic_url' => 'https://x/y.jpg']);
    $b = Profile::create(['instagram_username' => 'good', 'followers_count' => 1, 'profile_pic_url' => 'https://x/z.jpg']);

    dispatch_sync(new SendDigestJob);

    $bad = Profile::where('instagram_username', 'bad')->first();
    $good = Profile::where('instagram_username', 'good')->first();

    expect($bad->telegram_message_id)->toBeNull()
        ->and($good->telegram_message_id)->not->toBeNull();
});
