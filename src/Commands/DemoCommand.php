<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Commands;

use Illuminate\Console\Command;
use PlinCode\InstagramDigest\Contracts\CardRenderer;
use PlinCode\InstagramDigest\Facades\InstagramDigest;
use PlinCode\InstagramDigest\Models\Profile;
use PlinCode\InstagramDigest\Services\Telegram\TelegramClient;
use PlinCode\InstagramDigest\Support\ActionRegistry;

class DemoCommand extends Command
{
    protected $signature = 'instagram-digest:demo {--to= : Override chat id}';

    protected $description = 'Send a fake card to Telegram to verify your configuration.';

    public function handle(
        CardRenderer $renderer,
        TelegramClient $telegram,
        ActionRegistry $registry,
    ): int {
        $chatId = (string) ($this->option('to') ?: InstagramDigest::resolveChatId());

        $profile = new Profile([
            'id' => '00000000-0000-0000-0000-000000000000',
            'instagram_username' => 'demo_profile',
            'full_name' => 'Demo Profile',
            'biography' => 'This is a demo card from laravel-instagram-digest.',
            'followers_count' => 9999,
            'profile_pic_url' => 'https://placehold.co/600x400/png?text=laravel-instagram-digest',
            'source_hashtag' => 'demo',
            'status' => 'pending',
        ]);

        $payload = $renderer->render($profile, $registry->all());
        $telegram->sendPhoto($chatId, $payload);

        $this->info("Demo card sent to chat {$chatId}.");

        return self::SUCCESS;
    }
}
