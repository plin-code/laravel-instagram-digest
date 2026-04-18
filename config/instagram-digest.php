<?php

declare(strict_types=1);

use PlinCode\InstagramDigest\Models\Profile;
use PlinCode\InstagramDigest\Models\Run;

return [
    'apify' => [
        'token' => env('APIFY_TOKEN'),
        'actor_id' => env('APIFY_ACTOR_ID', 'apify~instagram-scraper'),
        'results_per_hashtag' => (int) env('APIFY_RESULTS_PER_HASHTAG', 30),
        'timeout_seconds' => (int) env('APIFY_TIMEOUT_SECONDS', 600),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
    ],

    'digest' => [
        'daily_count' => (int) env('INSTAGRAM_DIGEST_DAILY_COUNT', 20),
    ],

    'hashtags' => [],
    'keywords' => [],
    'min_followers' => (int) env('INSTAGRAM_DIGEST_MIN_FOLLOWERS', 5000),

    'queue' => env('INSTAGRAM_DIGEST_QUEUE', 'default'),

    'tables' => [
        'profiles' => 'instagram_digest_profiles',
        'runs' => 'instagram_digest_runs',
    ],

    'models' => [
        'profile' => Profile::class,
        'run' => Run::class,
    ],

    'route' => [
        'prefix' => 'instagram-digest',
        'middleware' => ['api'],
    ],
];
