<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use PlinCode\InstagramDigest\Services\Telegram\TelegramClient;
use PlinCode\InstagramDigest\Support\CardPayload;

beforeEach(function () {
    config()->set('instagram-digest.telegram.bot_token', 'BOT');
});

it('sendPhoto returns the telegram message_id', function () {
    Http::fake([
        'api.telegram.org/botBOT/sendPhoto' => Http::response(['ok' => true, 'result' => ['message_id' => 42]], 200),
    ]);

    $client = new TelegramClient;
    $id = $client->sendPhoto('CHAT', new CardPayload(
        caption: 'hi',
        photoUrl: 'https://example.com/p.jpg',
        buttons: [['text' => 'A', 'callback_data' => 'a:1']],
    ));

    expect($id)->toBe('42');
});

it('answerCallbackQuery posts to the right endpoint', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => true], 200)]);

    (new TelegramClient)->answerCallback('CBID', 'done');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/answerCallbackQuery');
    });
});

it('editMessageReplyMarkup clears the inline keyboard', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => true], 200)]);

    (new TelegramClient)->clearReplyMarkup('CHAT', 42);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/editMessageReplyMarkup');
    });
});

it('retries once after 429 respecting Retry-After', function () {
    config()->set('instagram-digest.telegram.bot_token', 'BOT');

    Http::fake([
        'api.telegram.org/*' => Http::sequence()
            ->push(['description' => 'Too Many Requests'], 429, ['Retry-After' => '1'])
            ->push(['ok' => true, 'result' => ['message_id' => 777]], 200),
    ]);

    $client = new TelegramClient;
    $id = $client->sendPhoto('CHAT', new CardPayload('hi', 'https://x/y.jpg', []));

    expect($id)->toBe('777');
});

it('falls back to sendMessage when photoUrl is null', function () {
    config()->set('instagram-digest.telegram.bot_token', 'BOT');
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 55]], 200)]);

    $client = new TelegramClient;
    $id = $client->sendPhoto('CHAT', new CardPayload(
        caption: 'text only',
        photoUrl: null,
        buttons: [],
    ));

    expect($id)->toBe('55');

    Http::assertSent(function ($r) {
        return str_contains($r->url(), '/sendMessage')
            && ($r->data()['text'] ?? null) === 'text only';
    });
});
