<?php

declare(strict_types=1);

namespace PlinCode\InstagramDigest\Actions;

use PlinCode\InstagramDigest\Contracts\DigestAction;
use PlinCode\InstagramDigest\Models\Profile;

class MarkAsInteresting implements DigestAction
{
    public function key(): string
    {
        return 'interesting';
    }

    public function label(): string
    {
        return (string) __('instagram-digest::actions.interesting');
    }

    public function handle(Profile $profile): void
    {
        $profile->update([
            'status' => 'interesting',
            'reviewed_at' => now(),
        ]);
    }
}
