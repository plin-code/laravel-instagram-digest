<?php

declare(strict_types=1);

use PlinCode\InstagramDigest\Models\Run;

it('persists a run with defaults', function () {
    $run = Run::create(['status' => 'pending']);

    expect($run->id)->not->toBeEmpty()
        ->and($run->profiles_found)->toBe(0)
        ->and($run->profiles_added)->toBe(0);
});

it('casts hashtags_scanned to array', function () {
    $run = Run::create([
        'status' => 'succeeded',
        'hashtags_scanned' => ['trekking', 'hiking'],
    ]);

    expect($run->fresh()->hashtags_scanned)->toBe(['trekking', 'hiking']);
});
