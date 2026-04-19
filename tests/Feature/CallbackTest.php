<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use PlinCode\InstagramDigest\Events\ProfileStatusChanged;
use PlinCode\InstagramDigest\Models\Profile;

beforeEach(function () {
    config()->set('instagram-digest.telegram.bot_token', 'BOT');
    config()->set('instagram-digest.telegram.webhook_secret', 'SECRET');
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => true], 200)]);
});

it('403 if secret mismatches', function () {
    $this->postJson('/instagram-digest/webhook/WRONG', [])
        ->assertStatus(403);
});

it('accepts callback_query with correct secret, updates profile status, emits event', function () {
    Event::fake([ProfileStatusChanged::class]);

    $profile = Profile::create(['instagram_username' => 'alice', 'followers_count' => 10]);

    $this->postJson('/instagram-digest/webhook/SECRET', [
        'callback_query' => [
            'id' => 'CB1',
            'data' => 'interesting:'.$profile->id,
            'message' => ['chat' => ['id' => '123'], 'message_id' => 42],
        ],
    ])->assertOk();

    expect($profile->fresh()->status)->toBe('interesting');
    Event::assertDispatched(ProfileStatusChanged::class);
});

it('200 OK (no-op) on unknown action key', function () {
    $profile = Profile::create(['instagram_username' => 'bob', 'followers_count' => 10]);

    $this->postJson('/instagram-digest/webhook/SECRET', [
        'callback_query' => [
            'id' => 'CB',
            'data' => 'nope:'.$profile->id,
            'message' => ['chat' => ['id' => '1'], 'message_id' => 1],
        ],
    ])->assertOk();

    expect($profile->fresh()->status)->toBe('pending');
});

it('accepts callback with secret in X-Telegram-Bot-Api-Secret-Token header', function () {
    Event::fake([ProfileStatusChanged::class]);

    $profile = Profile::create(['instagram_username' => 'hdr', 'followers_count' => 1]);

    // POST to the path WITHOUT a secret segment; secret travels in the header.
    $this->withHeaders(['X-Telegram-Bot-Api-Secret-Token' => 'SECRET'])
        ->postJson('/instagram-digest/webhook', [
            'callback_query' => [
                'id' => 'CBH',
                'data' => 'interesting:'.$profile->id,
                'message' => ['chat' => ['id' => '1'], 'message_id' => 1],
            ],
        ])->assertOk();

    expect($profile->fresh()->status)->toBe('interesting');
});

it('calls clearReplyMarkup with the originating chat_id and message_id', function () {
    $profile = Profile::create(['instagram_username' => 'clr', 'followers_count' => 1]);

    $this->postJson('/instagram-digest/webhook/SECRET', [
        'callback_query' => [
            'id' => 'CB',
            'data' => 'interesting:'.$profile->id,
            'message' => ['chat' => ['id' => 'CHAT_42'], 'message_id' => 909],
        ],
    ])->assertOk();

    Http::assertSent(function ($r) {
        $data = $r->data();

        return str_contains($r->url(), '/editMessageReplyMarkup')
            && ($data['chat_id'] ?? null) === 'CHAT_42'
            && ((int) ($data['message_id'] ?? 0)) === 909;
    });
});
