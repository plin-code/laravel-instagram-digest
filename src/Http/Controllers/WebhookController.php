<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use PlinCode\InstagramDigest\Events\ProfileStatusChanged;
use PlinCode\InstagramDigest\Models\Profile;
use PlinCode\InstagramDigest\Services\Telegram\TelegramClient;
use PlinCode\InstagramDigest\Support\ActionRegistry;
use Throwable;

class WebhookController extends Controller
{
    public function __construct(
        private readonly ActionRegistry $registry,
        private readonly TelegramClient $telegram,
    ) {}

    public function handle(Request $request, ?string $secret = null): JsonResponse
    {
        $expected = (string) config('instagram-digest.telegram.webhook_secret');

        if ($expected !== '') {
            $headerSecret = (string) $request->header('X-Telegram-Bot-Api-Secret-Token', '');
            $urlSecret = (string) $secret;

            if (! hash_equals($expected, $headerSecret) && ! hash_equals($expected, $urlSecret)) {
                return response()->json(['ok' => false], 403);
            }
        }

        try {
            $callback = $request->input('callback_query');
            if (! is_array($callback)) {
                return response()->json(['ok' => true]);
            }

            $data = (string) ($callback['data'] ?? '');
            [$key, $profileId] = array_pad(explode(':', $data, 2), 2, null);

            if ($key === null || $profileId === null) {
                return response()->json(['ok' => true]);
            }

            $action = $this->registry->find($key);
            if ($action === null) {
                Log::warning('instagram-digest: unknown action key', ['key' => $key]);

                return response()->json(['ok' => true]);
            }

            $profileModel = (string) config('instagram-digest.models.profile', Profile::class);
            $profile = $profileModel::find($profileId);
            if ($profile === null) {
                Log::warning('instagram-digest: profile not found', ['id' => $profileId]);

                return response()->json(['ok' => true]);
            }

            $from = (string) $profile->status;
            $action->handle($profile);
            $to = (string) $profile->fresh()->status;

            ProfileStatusChanged::dispatch($profile->fresh(), $from, $to);

            $callbackId = (string) ($callback['id'] ?? '');
            if ($callbackId !== '') {
                $this->telegram->answerCallback($callbackId, '✓ '.$action->label());
            }

            $chatId = (string) ($callback['message']['chat']['id'] ?? '');
            $messageId = $callback['message']['message_id'] ?? null;
            if ($chatId !== '' && $messageId !== null) {
                $this->telegram->clearReplyMarkup($chatId, $messageId);
            }
        } catch (Throwable $e) {
            Log::error('instagram-digest: webhook error', ['error' => $e->getMessage()]);
        }

        return response()->json(['ok' => true]);
    }
}
