<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('instagram-digest.telegram.bot_token', 'BOT');
    config()->set('instagram-digest.telegram.webhook_secret', 'SECRET');
});

it('registers the webhook at the given URL', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => true], 200)]);

    $this->artisan('instagram-digest:webhook', ['url' => 'https://example.com/instagram-digest/webhook/SECRET'])
        ->assertExitCode(0);

    Http::assertSent(fn ($r) => str_contains($r->url(), '/setWebhook'));
});

it('computes the default URL when none is passed', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => true], 200)]);
    config()->set('app.url', 'https://example.com');

    $this->artisan('instagram-digest:webhook')
        ->assertExitCode(0);

    Http::assertSent(fn ($r) => str_contains((string) ($r->data()['url'] ?? ''), 'example.com/instagram-digest/webhook/SECRET'));
});
