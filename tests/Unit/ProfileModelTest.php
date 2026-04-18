<?php

declare(strict_types=1);

use PlinCode\InstagramDigest\Models\Profile;

it('persists a profile with defaults', function () {
    $profile = Profile::create([
        'instagram_username' => 'trekking_italia',
        'followers_count' => 15000,
    ]);

    expect($profile->id)->not->toBeEmpty()
        ->and($profile->status)->toBe('pending')
        ->and($profile->is_verified)->toBeFalse()
        ->and($profile->metadata)->toBeNull();
});

it('casts metadata to array', function () {
    $profile = Profile::create([
        'instagram_username' => 'test_user',
        'followers_count' => 1000,
        'metadata' => ['custom' => 'data'],
    ]);

    expect($profile->fresh()->metadata)->toBe(['custom' => 'data']);
});
