<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Commands;

use Illuminate\Console\Command;
use PlinCode\InstagramDigest\Jobs\RunHashtagScrapingJob;

class ScrapeHashtagsCommand extends Command
{
    protected $signature = 'instagram-digest:scrape {--sync}';

    protected $description = 'Scrape Instagram hashtags via Apify and store profiles that pass the filters.';

    public function handle(): int
    {
        $job = new RunHashtagScrapingJob;

        if ($this->option('sync')) {
            dispatch_sync($job);
        } else {
            dispatch($job)->onQueue((string) config('instagram-digest.queue', 'default'));
        }

        $this->info('Scraping dispatched.');

        return self::SUCCESS;
    }
}
