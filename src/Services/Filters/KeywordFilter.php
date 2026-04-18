<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Services\Filters;

class KeywordFilter
{
    /**
     * @param  array<string>  $keywords
     */
    public function __construct(private readonly array $keywords) {}

    /**
     * @param  array{biography?: string|null, username?: string|null}  $profile
     */
    public function passes(array $profile): bool
    {
        if (empty($this->keywords)) {
            return true;
        }

        $haystack = mb_strtolower(
            ($profile['biography'] ?? '').' '.($profile['username'] ?? '')
        );

        foreach ($this->keywords as $keyword) {
            if ($keyword === '') {
                continue;
            }
            if (str_contains($haystack, mb_strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }
}
