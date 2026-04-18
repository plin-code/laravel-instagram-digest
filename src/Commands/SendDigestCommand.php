<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Commands;

use Illuminate\Console\Command;
use PlinCode\InstagramDigest\Jobs\SendDigestJob;

class SendDigestCommand extends Command
{
    protected $signature = 'instagram-digest:send {--count= : Override the configured daily count}';

    protected $description = 'Send the daily Telegram digest with pending profile cards.';

    public function handle(): int
    {
        $count = $this->option('count') !== null ? (int) $this->option('count') : null;

        dispatch(new SendDigestJob($count))
            ->onQueue((string) config('instagram-digest.queue', 'default'));

        $this->info('Digest dispatched.');

        return self::SUCCESS;
    }
}
