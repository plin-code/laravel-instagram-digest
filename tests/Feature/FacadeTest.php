<?php

declare(strict_types=1);

use PlinCode\InstagramDigest\Contracts\DigestAction;
use PlinCode\InstagramDigest\Facades\InstagramDigest;
use PlinCode\InstagramDigest\Models\Profile;
use PlinCode\InstagramDigest\Support\ActionRegistry;

it('resolves the facade and reports a version', function () {
    expect(InstagramDigest::version())->toBe('1.0.0');
});

it('returns the action registry singleton', function () {
    expect(InstagramDigest::registry())->toBeInstanceOf(ActionRegistry::class);
});

it('registerAction through the facade adds the action to the registry', function () {
    InstagramDigest::registerAction(
        key: 'snooze',
        label: 'Snooze',
        handler: fn (Profile $p) => $p->update(['status' => 'snoozed']),
    );

    expect(InstagramDigest::registry()->keys())->toContain('snooze');
});

it('defaultActions through the facade replaces the defaults', function () {
    InstagramDigest::defaultActions([
        new class implements DigestAction
        {
            public function key(): string
            {
                return 'only';
            }

            public function label(): string
            {
                return 'Only';
            }

            public function handle(Profile $p): void {}
        },
    ]);

    expect(InstagramDigest::registry()->keys())->toBe(['only']);
});
