<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Services\Telegram;

use Illuminate\Support\Facades\Http;
use PlinCode\InstagramDigest\Support\CardPayload;

class TelegramClient
{
    private readonly string $token;

    public function __construct()
    {
        $this->token = (string) config('instagram-digest.telegram.bot_token');
    }

    public function sendPhoto(string $chatId, CardPayload $payload): string
    {
        $body = [
            'chat_id' => $chatId,
            'caption' => $payload->caption,
            'parse_mode' => 'HTML',
        ];

        if (! empty($payload->buttons)) {
            $body['reply_markup'] = json_encode([
                'inline_keyboard' => array_chunk($payload->buttons, 2),
            ], JSON_THROW_ON_ERROR);
        }

        $endpoint = $payload->photoUrl !== null ? 'sendPhoto' : 'sendMessage';
        if ($endpoint === 'sendMessage') {
            $body['text'] = $body['caption'];
            unset($body['caption']);
        } else {
            $body['photo'] = $payload->photoUrl;
        }

        $response = Http::timeout(15)
            ->post("https://api.telegram.org/bot{$this->token}/{$endpoint}", $body);

        if ($response->status() === 429) {
            $retryAfter = (int) $response->header('Retry-After', '1');
            sleep(max(1, $retryAfter));
            $response = Http::timeout(15)->post("https://api.telegram.org/bot{$this->token}/{$endpoint}", $body);
        }

        $response->throw();

        return (string) $response->json('result.message_id');
    }

    public function answerCallback(string $callbackQueryId, string $text = ''): void
    {
        Http::timeout(10)->post("https://api.telegram.org/bot{$this->token}/answerCallbackQuery", [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
        ]);
    }

    public function clearReplyMarkup(string $chatId, int|string $messageId): void
    {
        Http::timeout(10)->post("https://api.telegram.org/bot{$this->token}/editMessageReplyMarkup", [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    public function setWebhook(string $url, ?string $secretToken = null): void
    {
        $body = ['url' => $url];
        if ($secretToken !== null && $secretToken !== '') {
            $body['secret_token'] = $secretToken;
        }

        Http::timeout(15)
            ->post("https://api.telegram.org/bot{$this->token}/setWebhook", $body)
            ->throw();
    }
}
