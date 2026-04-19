<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Services\Apify;

use PlinCode\InstagramDigest\Events\ProfileDiscovered;
use PlinCode\InstagramDigest\Models\Profile;
use PlinCode\InstagramDigest\Services\Filters\KeywordFilter;
use PlinCode\InstagramDigest\Services\Filters\MinFollowersFilter;

/**
 * @phpstan-type RawApifyProfile array{
 *     ownerUsername?: string|null,
 *     ownerFullName?: string|null,
 *     ownerBio?: string|null,
 *     ownerFollowersCount?: int|null,
 *     ownerProfilePicUrl?: string|null,
 *     ownerIsVerified?: bool|null,
 *     ownerId?: string|int|null,
 * }
 */
class HashtagScraper
{
    public function __construct(private readonly ApifyClient $client) {}

    /**
     * @param  array<string>  $hashtags
     * @param  array<string>  $keywords
     * @return array{found:int,added:int,updated:int}
     */
    public function scrape(array $hashtags, array $keywords, int $minFollowers): array
    {
        $found = 0;
        $added = 0;
        $updated = 0;

        $resultsLimit = (int) config('instagram-digest.apify.results_per_hashtag', 30);

        foreach ($hashtags as $hashtag) {
            $items = $this->client->runAndFetch([$hashtag], $resultsLimit);

            $profiles = $this->dedupeByUsername($items);

            $keywordFilter = new KeywordFilter($keywords);
            $followerFilter = new MinFollowersFilter($minFollowers);

            foreach ($profiles as $raw) {
                $found++;

                $normalized = [
                    'instagram_username' => mb_strtolower((string) ($raw['ownerUsername'] ?? '')),
                    'instagram_id' => isset($raw['ownerId']) ? (string) $raw['ownerId'] : null,
                    'full_name' => isset($raw['ownerFullName']) ? (string) $raw['ownerFullName'] : null,
                    'biography' => isset($raw['ownerBio']) ? (string) $raw['ownerBio'] : null,
                    'followers_count' => (int) ($raw['ownerFollowersCount'] ?? 0),
                    'profile_pic_url' => isset($raw['ownerProfilePicUrl']) ? (string) $raw['ownerProfilePicUrl'] : null,
                    'is_verified' => (bool) ($raw['ownerIsVerified'] ?? false),
                ];

                if ($normalized['instagram_username'] === '') {
                    continue;
                }

                if (! $keywordFilter->passes(['biography' => $normalized['biography'], 'username' => $normalized['instagram_username']])) {
                    continue;
                }
                if (! $followerFilter->passes(['followers_count' => $normalized['followers_count']])) {
                    continue;
                }

                // Per-profile lookup; acceptable for typical volumes (dozens per run). Batch via whereIn if volumes grow.
                $existing = Profile::where('instagram_username', $normalized['instagram_username'])->first();

                if ($existing === null) {
                    $profile = Profile::create($normalized + ['source_hashtag' => $hashtag]);
                    $added++;
                    event(new ProfileDiscovered($profile, isNew: true));
                } else {
                    $existing->fill($normalized);
                    if ($existing->isDirty()) {
                        $existing->save();
                        $updated++;
                    }
                    event(new ProfileDiscovered($existing, isNew: false));
                }
            }
        }

        return compact('found', 'added', 'updated');
    }

    /**
     * @param  array<int, RawApifyProfile>  $items
     * @return array<int, RawApifyProfile>
     */
    private function dedupeByUsername(array $items): array
    {
        $seen = [];
        $out = [];

        foreach ($items as $i) {
            $u = mb_strtolower((string) ($i['ownerUsername'] ?? ''));
            if ($u === '' || isset($seen[$u])) {
                continue;
            }
            $seen[$u] = true;
            $out[] = $i;
        }

        return $out;
    }
}
