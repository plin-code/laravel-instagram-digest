<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Services\Apify;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ApifyClient
{
    private const API_BASE = 'https://api.apify.com/v2';

    private readonly string $token;

    private readonly string $actorId;

    private readonly int $timeoutSeconds;

    public function __construct()
    {
        $this->token = (string) config('instagram-digest.apify.token');
        $this->actorId = (string) config('instagram-digest.apify.actor_id');
        $this->timeoutSeconds = (int) config('instagram-digest.apify.timeout_seconds', 600);
    }

    /**
     * @param  array<string>  $hashtags
     * @return array<int, array<string, mixed>>
     */
    public function runAndFetch(array $hashtags, int $resultsLimit): array
    {
        $runId = $this->startRun($hashtags, $resultsLimit);
        $this->waitForCompletion($runId);

        return $this->fetchItems($runId);
    }

    /**
     * @param  array<string>  $hashtags
     */
    private function startRun(array $hashtags, int $resultsLimit): string
    {
        $directUrls = array_map(
            fn (string $tag) => "https://www.instagram.com/explore/tags/{$tag}/",
            $hashtags,
        );

        $response = Http::withToken($this->token)
            ->timeout(30)
            ->post(self::API_BASE."/acts/{$this->actorId}/runs", [
                'directUrls' => $directUrls,
                'resultsType' => 'posts',
                'resultsLimit' => $resultsLimit,
            ])
            ->throw();

        return (string) $response->json('data.id');
    }

    private function waitForCompletion(string $runId): void
    {
        $deadline = time() + $this->timeoutSeconds;

        while (time() < $deadline) {
            $response = Http::withToken($this->token)
                ->timeout(30)
                ->get(self::API_BASE."/actor-runs/{$runId}")
                ->throw();

            $status = (string) $response->json('data.status');

            if ($status === 'SUCCEEDED') {
                return;
            }

            if (in_array($status, ['FAILED', 'ABORTED', 'TIMED-OUT'], true)) {
                throw new RuntimeException("Apify run failed with status {$status}");
            }

            sleep(2);
        }

        throw new RuntimeException('Apify run timed out');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchItems(string $runId): array
    {
        return (array) Http::withToken($this->token)
            ->timeout(30)
            ->get(self::API_BASE."/actor-runs/{$runId}/dataset/items")
            ->throw()
            ->json();
    }
}
