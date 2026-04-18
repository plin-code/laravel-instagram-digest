<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PlinCode\InstagramDigest\Contracts\CardRenderer;
use PlinCode\InstagramDigest\Events\DigestSent;
use PlinCode\InstagramDigest\Facades\InstagramDigest;
use PlinCode\InstagramDigest\Models\Profile;
use PlinCode\InstagramDigest\Services\Telegram\TelegramClient;
use PlinCode\InstagramDigest\Support\ActionRegistry;
use Throwable;

class SendDigestJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly ?int $countOverride = null) {}

    public function handle(
        CardRenderer $renderer,
        TelegramClient $telegram,
        ActionRegistry $registry,
    ): void {
        $chatId = InstagramDigest::resolveChatId();
        $count = $this->countOverride ?? InstagramDigest::resolveDailyCount();

        $profiles = Profile::where('status', 'pending')
            ->whereNull('telegram_message_id')
            ->orderBy('created_at')
            ->limit($count)
            ->get();

        $sentIds = [];

        foreach ($profiles as $profile) {
            try {
                $payload = $renderer->render($profile, $registry->all());
                $messageId = $telegram->sendPhoto($chatId, $payload);

                $profile->update(['telegram_message_id' => $messageId]);
                $sentIds[] = $profile->id;
            } catch (Throwable $e) {
                Log::warning('instagram-digest: failed to send card', [
                    'profile' => $profile->instagram_username,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        DigestSent::dispatch($sentIds);
    }
}
