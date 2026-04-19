<?php

declare(strict_types=1);

use PlinCode\InstagramDigest\Actions\MarkAsInteresting;
use PlinCode\InstagramDigest\Contracts\DigestAction;
use PlinCode\InstagramDigest\Facades\InstagramDigest;
use PlinCode\InstagramDigest\Models\Profile;
use PlinCode\InstagramDigest\Support\ActionRegistry;

it('ships with 3 default actions', function () {
    $registry = app(ActionRegistry::class);

    expect($registry->keys())->toEqualCanonicalizing(['interesting', 'rejected', 'repropose']);
});

it('can find an action by key', function () {
    $registry = app(ActionRegistry::class);

    expect($registry->find('interesting'))->toBeInstanceOf(MarkAsInteresting::class)
        ->and($registry->find('unknown'))->toBeNull();
});

it('can register a custom action via closure', function () {
    InstagramDigest::registerAction(
        key: 'archive',
        label: 'Archive',
        handler: fn (Profile $p) => $p->update(['status' => 'archived']),
    );

    $registry = app(ActionRegistry::class);

    expect($registry->keys())->toContain('archive');

    $action = $registry->find('archive');
    $p = Profile::create(['instagram_username' => 'x', 'followers_count' => 1]);
    $action->handle($p);

    expect($p->fresh()->status)->toBe('archived');
});

it('can replace default actions with defaultActions()', function () {
    InstagramDigest::defaultActions([
        new class implements DigestAction
        {
            public function key(): string
            {
                return 'yes';
            }

            public function label(): string
            {
                return 'Yes';
            }

            public function handle(Profile $p): void
            {
                $p->update(['status' => 'yes']);
            }
        },
    ]);

    $registry = app(ActionRegistry::class);

    expect($registry->keys())->toBe(['yes']);
});
