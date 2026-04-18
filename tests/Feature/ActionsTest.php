<?php

declare(strict_types=1);

use PlinCode\InstagramDigest\Actions\MarkAsInteresting;
use PlinCode\InstagramDigest\Actions\MarkAsRejected;
use PlinCode\InstagramDigest\Actions\ReproposeAction;
use PlinCode\InstagramDigest\Models\Profile;

it('MarkAsInteresting sets status and reviewed_at', function () {
    $p = Profile::create(['instagram_username' => 'a', 'followers_count' => 10]);

    (new MarkAsInteresting)->handle($p);

    expect($p->fresh()->status)->toBe('interesting')
        ->and($p->fresh()->reviewed_at)->not->toBeNull();
});

it('MarkAsRejected sets status to rejected', function () {
    $p = Profile::create(['instagram_username' => 'b', 'followers_count' => 10]);

    (new MarkAsRejected)->handle($p);

    expect($p->fresh()->status)->toBe('rejected');
});

it('ReproposeAction sets status back to pending and clears telegram_message_id', function () {
    $p = Profile::create([
        'instagram_username' => 'c',
        'followers_count' => 10,
        'status' => 'interesting',
        'telegram_message_id' => '42',
    ]);

    (new ReproposeAction)->handle($p);

    expect($p->fresh()->status)->toBe('pending')
        ->and($p->fresh()->telegram_message_id)->toBeNull();
});

it('exposes keys and labels', function () {
    expect((new MarkAsInteresting)->key())->toBe('interesting')
        ->and((new MarkAsRejected)->key())->toBe('rejected')
        ->and((new ReproposeAction)->key())->toBe('repropose')
        ->and((new MarkAsInteresting)->label())->toContain('Interesting');
});
