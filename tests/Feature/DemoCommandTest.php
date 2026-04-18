<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('instagram-digest.telegram.bot_token', 'BOT');
    config()->set('instagram-digest.telegram.chat_id', 'CHAT');
});

it('sends a fake card to the configured chat id', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]], 200)]);

    $this->artisan('instagram-digest:demo')->assertExitCode(0);

    Http::assertSent(function ($r) {
        $data = $r->data();
        return str_contains($r->url(), '/botBOT/send') && ($data['chat_id'] ?? null) === 'CHAT';
    });
});

it('honors --to override', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 1]], 200)]);

    $this->artisan('instagram-digest:demo', ['--to' => 'OTHER'])->assertExitCode(0);

    Http::assertSent(function ($r) {
        $data = $r->data();
        return ($data['chat_id'] ?? null) === 'OTHER';
    });
});
