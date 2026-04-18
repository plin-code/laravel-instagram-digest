<?php

declare(strict_types=1);

use PlinCode\InstagramDigest\Actions\MarkAsInteresting;
use PlinCode\InstagramDigest\Actions\MarkAsRejected;
use PlinCode\InstagramDigest\Contracts\CardRenderer;
use PlinCode\InstagramDigest\Facades\InstagramDigest;
use PlinCode\InstagramDigest\Models\Profile;
use PlinCode\InstagramDigest\Support\CardPayload;

it('renders a default card with caption, photo and action buttons', function () {
    $profile = Profile::create([
        'instagram_username' => 'alice',
        'full_name' => 'Alice',
        'followers_count' => 12000,
        'biography' => 'Love trekking',
        'profile_pic_url' => 'https://example.com/a.jpg',
        'source_hashtag' => 'trekking',
    ]);

    $renderer = app(CardRenderer::class);
    $payload = $renderer->render($profile, [new MarkAsInteresting, new MarkAsRejected]);

    expect($payload)->toBeInstanceOf(CardPayload::class)
        ->and($payload->photoUrl)->toBe('https://example.com/a.jpg')
        ->and($payload->caption)->toContain('Alice')->toContain('12,000')
        ->and($payload->buttons)->toHaveCount(2)
        ->and($payload->buttons[0]['callback_data'])->toStartWith('interesting:');
});

it('respects renderCardUsing override via direct bind', function () {
    app()->bind(CardRenderer::class, function () {
        return new class implements CardRenderer
        {
            public function render(Profile $profile, array $actions): CardPayload
            {
                return new CardPayload('OVERRIDE', null, []);
            }
        };
    });

    $profile = Profile::create(['instagram_username' => 'x', 'followers_count' => 1]);

    expect(app(CardRenderer::class)->render($profile, [])->caption)->toBe('OVERRIDE');
});

it('renderCardUsing(ClassName::class) rebinds the CardRenderer contract', function () {
    InstagramDigest::renderCardUsing(\PlinCode\InstagramDigest\Rendering\DefaultCardRenderer::class);

    expect(app(CardRenderer::class))->toBeInstanceOf(\PlinCode\InstagramDigest\Rendering\DefaultCardRenderer::class);
});
