<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Services\Filters;

class MinFollowersFilter
{
    public function __construct(private readonly int $threshold) {}

    /**
     * @param  array{followers_count?: int|null}  $profile
     */
    public function passes(array $profile): bool
    {
        return ((int) ($profile['followers_count'] ?? 0)) >= $this->threshold;
    }
}
