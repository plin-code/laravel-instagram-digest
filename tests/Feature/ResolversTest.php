<?php

declare(strict_types=1);

use PlinCode\InstagramDigest\Facades\InstagramDigest;

it('falls back to config when no hashtags resolver is registered', function () {
    config()->set('instagram-digest.hashtags', ['trekking', 'hiking']);

    expect(InstagramDigest::resolveHashtags())->toBe(['trekking', 'hiking']);
});

it('uses the registered hashtags resolver over config', function () {
    config()->set('instagram-digest.hashtags', ['fallback']);
    InstagramDigest::hashtagsUsing(fn () => ['from-closure']);

    expect(InstagramDigest::resolveHashtags())->toBe(['from-closure']);
});

it('falls back to config for keywords, min-followers, chat-id, daily-count', function () {
    config()->set('instagram-digest.keywords', ['trek']);
    config()->set('instagram-digest.min_followers', 7777);
    config()->set('instagram-digest.telegram.chat_id', '-100');
    config()->set('instagram-digest.digest.daily_count', 42);

    expect(InstagramDigest::resolveKeywords())->toBe(['trek'])
        ->and(InstagramDigest::resolveMinFollowers())->toBe(7777)
        ->and(InstagramDigest::resolveChatId())->toBe('-100')
        ->and(InstagramDigest::resolveDailyCount())->toBe(42);
});

it('lets resolver override config', function () {
    InstagramDigest::keywordsUsing(fn () => ['guida']);
    InstagramDigest::minFollowersUsing(fn () => 1234);
    InstagramDigest::chatIdUsing(fn () => '999');
    InstagramDigest::dailyCountUsing(fn () => 5);

    expect(InstagramDigest::resolveKeywords())->toBe(['guida'])
        ->and(InstagramDigest::resolveMinFollowers())->toBe(1234)
        ->and(InstagramDigest::resolveChatId())->toBe('999')
        ->and(InstagramDigest::resolveDailyCount())->toBe(5);
});
