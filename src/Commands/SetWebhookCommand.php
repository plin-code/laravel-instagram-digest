<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Commands;

use Illuminate\Console\Command;
use PlinCode\InstagramDigest\Services\Telegram\TelegramClient;

class SetWebhookCommand extends Command
{
    protected $signature = 'instagram-digest:webhook {url?}';

    protected $description = 'Register the Telegram webhook URL with Telegram.';

    public function handle(TelegramClient $telegram): int
    {
        $secret = (string) config('instagram-digest.telegram.webhook_secret', '');
        $prefix = (string) config('instagram-digest.route.prefix', 'instagram-digest');

        $url = $this->argument('url') ?: rtrim((string) config('app.url'), '/')."/{$prefix}/webhook".($secret !== '' ? "/{$secret}" : '');

        $telegram->setWebhook($url, $secret !== '' ? $secret : null);

        $this->info("Webhook set to: {$url}");

        return self::SUCCESS;
    }
}
