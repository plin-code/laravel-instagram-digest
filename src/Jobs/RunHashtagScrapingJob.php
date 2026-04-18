<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PlinCode\InstagramDigest\Events\ScrapingRunCompleted;
use PlinCode\InstagramDigest\Facades\InstagramDigest;
use PlinCode\InstagramDigest\Models\Run;
use PlinCode\InstagramDigest\Services\Apify\HashtagScraper;
use Throwable;

class RunHashtagScrapingJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 900;

    public int $tries = 3;

    /** @return array<int> */
    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function handle(HashtagScraper $scraper): void
    {
        $hashtags = InstagramDigest::resolveHashtags();
        $keywords = InstagramDigest::resolveKeywords();
        $minFollowers = InstagramDigest::resolveMinFollowers();

        $run = Run::create([
            'status' => 'running',
            'started_at' => now(),
            'hashtags_scanned' => $hashtags,
        ]);

        try {
            $counts = $scraper->scrape($hashtags, $keywords, $minFollowers);

            $run->update([
                'status' => 'succeeded',
                'finished_at' => now(),
                'profiles_found' => $counts['found'],
                'profiles_added' => $counts['added'],
                'profiles_updated' => $counts['updated'],
            ]);

            ScrapingRunCompleted::dispatch($run->fresh());
        } catch (Throwable $e) {
            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
